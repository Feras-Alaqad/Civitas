<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminRole
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(403);
        }

        $adminRoleId = Cache::rememberForever('roles:admin_id', function () {
            return DB::table('roles')->where('RoleName', 'Admin')->value('RoleID');
        });

        if ($adminRoleId === null || (string) $user->RoleID !== (string) $adminRoleId) {
            abort(403, 'Administrator access is required.');
        }

        return $next($request);
    }
}