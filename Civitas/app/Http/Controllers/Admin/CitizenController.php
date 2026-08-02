<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Person;
use App\Services\CitizensCacheService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class CitizenController extends Controller
{
    private CitizensCacheService $cacheService;

    public function __construct(CitizensCacheService $cacheService)
    {
        $this->cacheService = $cacheService;
    }

    public function index(Request $request)
    {
        $start = microtime(true);

        $search = $this->sanitizeSearch($request->input('search'));
        $personId = $request->input('person_id');
        $page = max(1, (int) $request->input('page', 1));

        if ($search || $personId) {
            AuditLog::create([
                'UserID' => Auth::id(),
                'ActionType' => 'PersonSearch',
                'Description' => $search ?: "Viewed person: {$personId}",
                'Timestamp' => now(),
                'IPAddress' => $request->ip(),
            ]);
        }

        if ($personId) {
            return $this->showPerson($personId, $search, $start);
        }

        if ($search) {
            $redirect = $this->tryExactRedirect($search);
            if ($redirect) {
                return $redirect;
            }
        }

        if ($search) {
            $searchType = $this->detectSearchType($search);

            if ($searchType === 'name') {
                $result = $this->searchViaMeilisearch($search, $page, $request);
            } else {
                $result = $this->searchViaDatabase($search, $page, $request);
            }
        } else {
            $offset = ($page - 1) * CitizensCacheService::PER_PAGE;
            $cached = $this->cacheService->getCachedRows($search, $offset);
            $total = $this->cacheService->getCachedTotalCount();

            $result = [
                'citizens' => new LengthAwarePaginator(
                    $cached['rows'],
                    $total,
                    CitizensCacheService::PER_PAGE,
                    $page,
                    ['path' => LengthAwarePaginator::resolveCurrentPath(), 'query' => $request->only('search')]
                ),
            ];
        }

        return view('admin.citizens', [
            'citizens' => $result['citizens'],
            'currentPage' => $page,
            'selectedPerson' => null,
            'serviceRequests' => collect([]),
            'search' => $search,
            'loadTimeMs' => round((microtime(true) - $start) * 1000, 2),
        ]);
    }

    private function searchViaMeilisearch(string $search, int $page, Request $request): array
    {
        $cached = $this->cacheService->getCachedMeilisearchRows($search, $page);

        if ($cached) {
            $rows = collect($cached['rows'])->map(fn ($row) => (object) $row);
            $realTotal = $cached['total'] ?? $this->cacheService->getCachedMeilisearchTotal($search);
        } else {
            $results = Person::search($search)->paginate(
                CitizensCacheService::PER_PAGE,
                'page',
                $page
            );
            $results->getCollection()->load('governorate', 'nationality');
            $hits = $results->getCollection()->toArray();
            $realTotal = (int) $results->total();

            $this->cacheService->putCachedMeilisearchTotal($search, $realTotal);
            $this->cacheService->putCachedMeilisearchRows($search, $page, [
                'rows' => $hits, 'total' => $realTotal, 'cursor' => null,
            ]);

            $rows = collect($hits)->map(fn ($row) => (object) $row);
        }

        $paginator = new LengthAwarePaginator(
            $rows,
            $realTotal,
            CitizensCacheService::PER_PAGE,
            $page,
            ['path' => $request->url(), 'query' => ['search' => $search]]
        );

        return [
            'citizens' => $paginator,
        ];
    }

    private function searchViaDatabase(string $search, int $page, Request $request): array
    {
        $offset = ($page - 1) * CitizensCacheService::PER_PAGE;
        $result = $this->cacheService->searchCachedRows($search, $offset);

        $count = count($result['rows']);
        if ($result['hasMore']) {
            $total = $page * CitizensCacheService::PER_PAGE + 1;
        } else {
            $total = ($page - 1) * CitizensCacheService::PER_PAGE + $count;
        }

        $paginator = new LengthAwarePaginator(
            $result['rows'],
            $total,
            CitizensCacheService::PER_PAGE,
            $page,
            ['path' => LengthAwarePaginator::resolveCurrentPath(), 'query' => $request->only('search')]
        );

        return [
            'citizens' => $paginator,
        ];
    }

    private function detectSearchType(string $search): string
    {
        if (filter_var($search, FILTER_VALIDATE_EMAIL)) {
            return 'email';
        }

        if (preg_match('/^\d+$/', $search)) {
            return 'id';
        }

        return 'name';
    }

    private function sanitizeSearch(?string $value): string
    {
        $value = trim($value ?? '');
        return str_replace(['"', "'"], '', $value);
    }

    private function showPerson(string $personId, string $search, float $start)
    {
        $person = $this->cacheService->getCachedPerson($personId);

        if (!$person) {
            return redirect()->route('admin.citizens')->with('error', 'Person not found.');
        }

        return view('admin.citizens', [
            'selectedPerson' => $person,
            'serviceRequests' => $this->cacheService->getCachedServiceRequests($personId),
            'citizens' => null,
            'currentPage' => 1,
            'search' => $search,
            'loadTimeMs' => round((microtime(true) - $start) * 1000, 2),
        ]);
    }

    private function tryExactRedirect(string $search): ?\Illuminate\Http\RedirectResponse
    {
        if (filter_var($search, FILTER_VALIDATE_EMAIL)) {
            $match = $this->cacheService->findExactMatch('Email', $search);
        } elseif (preg_match('/^\d+$/', $search)) {
            $match = $this->cacheService->findExactNationalId($search);
        } else {
            return null;
        }

        if ($match) {
            return redirect()->route('admin.citizens', [
                'person_id' => $match->PersonID,
                'search' => $search,
            ]);
        }

        return null;
    }
}
