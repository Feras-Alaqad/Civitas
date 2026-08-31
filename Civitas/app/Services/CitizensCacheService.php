<?php

namespace App\Services;

use App\Models\Person;
use Illuminate\Cache\DatabaseStore;
use Illuminate\Cache\RedisStore;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CitizensCacheService
{
    public const PER_PAGE = 15;

    public const TTL_LIST = 300;

    public const TTL_PERSON = 600;

    public const NULL_SENTINEL = '__';

    public function buildListCacheKey(string $search, int $offset): string
    {
        return 'citizens:list:'.md5($search).":{$offset}";
    }

    public function buildPersonCacheKey(string $personId): string
    {
        return "citizens:person:{$personId}";
    }

    public function buildNidCacheKey(string $nationalId): string
    {
        return "citizens:nid:{$nationalId}";
    }

    public function buildExactCacheKey(string $column, string $search): string
    {
        return "citizens:exact:{$column}:{$search}";
    }

    public function buildRequestsCacheKey(string $personId): string
    {
        return "citizens:requests:{$personId}";
    }

    public function getCachedTotalCount(): int
    {
        $cacheKey = 'citizens:total_count';

        return (int) Cache::rememberForever($cacheKey, function () {
            return DB::table('Persons')->count();
        });
    }

    public function buildMeilisearchCacheKey(string $search, int $page): string
    {
        return 'citizens:meilisearch:'.md5($search).":{$page}";
    }

    public function getCachedMeilisearchRows(string $search, int $page): ?array
    {
        $cacheKey = $this->buildMeilisearchCacheKey($search, $page);

        $cached = Cache::get($cacheKey);

        return $cached ? json_decode($cached, true) : null;
    }

    public function putCachedMeilisearchRows(string $search, int $page, array $data): void
    {
        $cacheKey = $this->buildMeilisearchCacheKey($search, $page);

        Cache::forever($cacheKey, json_encode($data));
    }

    public function getCachedMeilisearchTotal(string $search): int
    {
        $cacheKey = 'citizens:meilisearch_total:'.md5($search);

        return (int) Cache::get($cacheKey, 0);
    }

    public function putCachedMeilisearchTotal(string $search, int $total): void
    {
        $cacheKey = 'citizens:meilisearch_total:'.md5($search);
        Cache::forever($cacheKey, $total);
    }

    public function searchCachedRows(string $search, int $offset): array
    {
        $cacheKey = $this->buildListCacheKey($search, $offset);

        $cached = Cache::rememberForever($cacheKey, function () use ($search, $offset) {
            $type = $this->detectSearchType($search);

            $personIds = collect();

            if ($type === 'email') {
                $personIds = DB::table('Persons')
                    ->where('Email', $search)
                    ->limit(self::PER_PAGE + 1)->skip($offset)
                    ->pluck('PersonID');
            } elseif ($type === 'national_id') {
                $personIds = DB::table('Persons')
                    ->where('NationalID', $search)
                    ->limit(self::PER_PAGE + 1)->skip($offset)
                    ->pluck('PersonID');
            } elseif ($type === 'phone') {
                $personIds = DB::table('Persons')
                    ->where('Phone', $search)
                    ->limit(self::PER_PAGE + 1)->skip($offset)
                    ->pluck('PersonID');
            } else {
                $keywords = $this->buildFulltextKeywords($search);

                $personIds = DB::table('Persons')
                    ->select('PersonID')
                    ->whereRaw('MATCH(FullNameSearch) AGAINST(? IN BOOLEAN MODE)', [$keywords])
                    ->orderBy('PersonID', 'desc')
                    ->limit(self::PER_PAGE + 1)->skip($offset)
                    ->pluck('PersonID');
            }

            $hasMore = $personIds->count() > self::PER_PAGE;
            $personIds = $personIds->take(self::PER_PAGE);

            if ($personIds->isEmpty()) {
                return json_encode(['rows' => [], 'hasMore' => false]);
            }

            $rows = DB::table('Persons')
                ->leftJoin('Governorates', 'Governorates.GovernorateID', '=', 'Persons.GovernorateID')
                ->leftJoin('Nationalities', 'Nationalities.NationalityID', '=', 'Persons.NationalityID')
                ->select(
                    'Persons.PersonID', 'Persons.FullName', 'Persons.NationalID',
                    'Persons.Gender', 'Persons.DateOfBirth', 'Persons.Phone',
                    'Persons.Email', 'Persons.Address',
                    'Governorates.GovernorateName', 'Nationalities.NationalityName',
                )
                ->whereIn('Persons.PersonID', $personIds)
                ->get();

            return json_encode([
                'rows' => $rows->values()->all(),
                'hasMore' => $hasMore,
            ]);
        });

        $data = json_decode($cached);

        return [
            'rows' => $data->rows ?? [],
            'hasMore' => $data->hasMore ?? false,
        ];
    }

    private function detectSearchType(string $search): string
    {
        if (filter_var($search, FILTER_VALIDATE_EMAIL)) {
            return 'email';
        }

        if (preg_match('/^\d+$/', $search)) {
            $len = strlen($search);
            if ($len === 9) {
                return 'national_id';
            }
            if ($len === 10) {
                return 'phone';
            }
        }

        return 'name';
    }

    private function buildFulltextKeywords(string $search): string
    {
        $normalized = Person::normalizeName($search);

        $words = collect(preg_split('/\s+/', $normalized))
            ->filter()
            ->values();

        return $words
            ->map(fn ($word) => '+'.$word.'*')
            ->implode(' ');
    }

    public static function flushCitizensCache(): void
    {
        $store = Cache::getStore();

        if ($store instanceof RedisStore) {
            $client = $store->connection()->client();
            $prefix = $store->getPrefix();
            $cursor = null;
            $keys = [];

            do {
                $batch = $client->scan($cursor, $prefix.'citizens:*', 200);
                $keys = array_merge($keys, is_array($batch) ? $batch : []);
            } while ((int) $cursor > 0);

            foreach (array_chunk($keys, 500) as $chunk) {
                $client->del($chunk);
            }

            return;
        }

        if ($store instanceof DatabaseStore) {
            $prefix = config('cache.prefix', '');
            DB::table('cache')->where('key', 'LIKE', $prefix.'citizens:%')->delete();

            return;
        }

        Cache::flush();
    }

    public function warmMeilisearchPage(string $search, int $page): void
    {
        $results = Person::search($search)->paginate(
            self::PER_PAGE,
            'page',
            $page
        );
        $results->getCollection()->load('governorate', 'nationality');
        $hits = $results->getCollection()->toArray();
        $total = (int) $results->total();

        $lastPersonId = null;
        if (! empty($hits)) {
            $lastItem = end($hits);
            $lastPersonId = is_array($lastItem) ? ($lastItem['PersonID'] ?? null) : ($lastItem->PersonID ?? null);
        }

        $this->putCachedMeilisearchRows($search, $page, [
            'rows' => $hits,
            'total' => $total,
            'cursor' => $lastPersonId,
        ]);
    }

    public function warmListPage(string $search, int $offset): void
    {
        $cacheKey = $this->buildListCacheKey($search, $offset);

        $query = DB::table('Persons')
            ->leftJoin('Governorates', 'Governorates.GovernorateID', '=', 'Persons.GovernorateID')
            ->leftJoin('Nationalities', 'Nationalities.NationalityID', '=', 'Persons.NationalityID')
            ->select(
                'Persons.PersonID', 'Persons.FullName', 'Persons.NationalID',
                'Persons.Gender', 'Persons.DateOfBirth', 'Persons.Phone',
                'Persons.Email', 'Persons.Address',
                'Governorates.GovernorateName', 'Nationalities.NationalityName',
            );

        $this->applySearchFilter($query, $search);

        $rows = $query->limit(self::PER_PAGE + 1)->skip($offset)->get();
        $hasMore = $rows->count() > self::PER_PAGE;

        Cache::forever($cacheKey, json_encode([
            'rows' => $rows->take(self::PER_PAGE)->values()->all(),
            'hasMore' => $hasMore,
        ]));
    }

    public function getCachedRows(string $search, int $offset): array
    {
        $cacheKey = $this->buildListCacheKey($search, $offset);

        $cached = Cache::rememberForever($cacheKey, function () use ($search, $offset) {
            $query = DB::table('Persons')
                ->leftJoin('Governorates', 'Governorates.GovernorateID', '=', 'Persons.GovernorateID')
                ->leftJoin('Nationalities', 'Nationalities.NationalityID', '=', 'Persons.NationalityID')
                ->select(
                    'Persons.PersonID', 'Persons.FullName', 'Persons.NationalID',
                    'Persons.Gender', 'Persons.DateOfBirth', 'Persons.Phone',
                    'Persons.Email', 'Persons.Address',
                    'Governorates.GovernorateName', 'Nationalities.NationalityName',
                );

            $this->applySearchFilter($query, $search);

            $rows = $query->limit(self::PER_PAGE + 1)->skip($offset)->get();
            $hasMore = $rows->count() > self::PER_PAGE;

            return json_encode([
                'rows' => $rows->take(self::PER_PAGE)->values()->all(),
                'hasMore' => $hasMore,
            ]);
        });

        $data = json_decode($cached);

        return [
            'rows' => $data->rows ?? [],
            'hasMore' => $data->hasMore ?? false,
        ];
    }

    public function getCachedPerson(string $personId): ?object
    {
        $cacheKey = $this->buildPersonCacheKey($personId);

        $cached = Cache::rememberForever($cacheKey, function () use ($personId) {
            $row = DB::table('Persons')
                ->leftJoin('Governorates', 'Governorates.GovernorateID', '=', 'Persons.GovernorateID')
                ->leftJoin('Cities', 'Cities.CityID', '=', 'Persons.CityID')
                ->leftJoin('Nationalities', 'Nationalities.NationalityID', '=', 'Persons.NationalityID')
                ->select(
                    'Persons.PersonID', 'Persons.FullName', 'Persons.NationalID',
                    'Persons.Gender', 'Persons.DateOfBirth', 'Persons.Phone',
                    'Persons.Email', 'Persons.Address',
                    'Governorates.GovernorateName', 'Cities.CityName', 'Nationalities.NationalityName',
                )
                ->where('Persons.PersonID', $personId)
                ->first();

            return $row ? json_encode($row) : self::NULL_SENTINEL;
        });

        return $cached === self::NULL_SENTINEL ? null : json_decode($cached);
    }

    public function getCachedServiceRequests(string $personId): Collection
    {
        $cacheKey = $this->buildRequestsCacheKey($personId);

        $cached = Cache::rememberForever($cacheKey, function () use ($personId) {
            $rows = DB::table('Service_Requests')
                ->join('Service_Types', 'Service_Types.ServiceTypeID', '=', 'Service_Requests.ServiceTypeID')
                ->leftJoin('Departments', 'Departments.DepartmentID', '=', 'Service_Types.DepartmentID')
                ->leftJoin('Payments', function ($join) {
                    $join->on('Payments.RequestID', '=', 'Service_Requests.RequestID')
                        ->where('Payments.Status', 'pending');
                })
                ->where('Service_Requests.PersonID', $personId)
                ->select(
                    'Service_Requests.RequestID', 'Service_Requests.RequestDate',
                    'Service_Requests.Status', 'Service_Requests.created_at', 'Service_Requests.updated_at',
                    'Service_Types.ServiceName', 'Service_Types.Fees', 'Service_Types.RequiredDocuments',
                    'Departments.DepartmentName',
                    'Payments.StripePaymentIntentID', 'Payments.LahzaReference',
                )
                ->orderByDesc('Service_Requests.RequestDate')
                ->get();

            return json_encode($rows);
        });

        return collect(json_decode($cached));
    }

    public function findExactNationalId(string $search): ?object
    {
        $cacheKey = $this->buildNidCacheKey($search);

        $cached = Cache::rememberForever($cacheKey, function () use ($search) {
            $row = DB::table('Persons')
                ->where('Persons.NationalID', $search)
                ->select('PersonID')
                ->first();

            return $row ? json_encode($row) : self::NULL_SENTINEL;
        });

        return $cached === self::NULL_SENTINEL ? null : json_decode($cached);
    }

    public function findExactMatch(string $column, string $search): ?object
    {
        $cacheKey = $this->buildExactCacheKey($column, $search);

        $cached = Cache::rememberForever($cacheKey, function () use ($column, $search) {
            $row = DB::table('Persons')
                ->where("Persons.{$column}", $search)
                ->select('PersonID')
                ->first();

            return $row ? json_encode($row) : self::NULL_SENTINEL;
        });

        return $cached === self::NULL_SENTINEL ? null : json_decode($cached);
    }

    public function applySearchFilter($query, string $search): void
    {
        $search = trim($search);

        if ($search === '') {
            return;
        }

        if (filter_var($search, FILTER_VALIDATE_EMAIL)) {
            $query->where('Persons.Email', $search);

            return;
        }

        if (preg_match('/^\d+$/', $search)) {
            $len = strlen($search);
            if ($len === 9) {
                $query->where('Persons.NationalID', $search);
            } elseif ($len === 10) {
                $query->where('Persons.Phone', $search);
            } else {
                $query->where(fn ($q) => $q
                    ->where('Persons.Phone', $search)
                    ->orWhere('Persons.NationalID', $search)
                );
            }

            return;
        }

        $normalized = Person::normalizeName($search);

        $words = collect(preg_split('/\s+/', $normalized))
            ->filter()
            ->values();

        $keywords = $words
            ->map(fn ($word) => '+'.$word.'*')
            ->implode(' ');

        $query->whereRaw(
            'MATCH(Persons.FullNameSearch) AGAINST(? IN BOOLEAN MODE)',
            [$keywords]
        );

        $query->orderByRaw(
            'MATCH(Persons.FullNameSearch) AGAINST(? IN BOOLEAN MODE) DESC',
            [$keywords]
        );
    }
}
