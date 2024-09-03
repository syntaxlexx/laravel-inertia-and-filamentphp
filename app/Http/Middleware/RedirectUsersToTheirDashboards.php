<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Str;

class RedirectUsersToTheirDashboards
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $rolesToIgnore): Response
    {
        $user = request()->user();

        if (!$user) {
            abort(401);
        }

        $rolesToIgnore = Str::replace(' ', '', $rolesToIgnore);
        $rolesToIgnore = Str::contains($rolesToIgnore, ',') ? explode(',', $rolesToIgnore) : explode('|', $rolesToIgnore);

        /**
         * if user role DOESNT exists in the rolesToIgnore array, redirect to their dashboard
         */
        if (!in_array($user->role, $rolesToIgnore)) {
            switch($user->role) {
                case User::ROLE_USER:
                    return redirect()->route('dashboard');
                case User::ROLE_ADMIN:
                case User::ROLE_SUPERADMIN:
                    return redirect()->route('admin.dashboard');
                default:
                    abort(403);
            }
        }

        return $next($request);
    }
}
