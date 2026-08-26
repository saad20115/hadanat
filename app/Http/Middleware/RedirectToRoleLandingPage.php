<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Webkul\Security\Models\Role;

class RedirectToRoleLandingPage
{
    public function handle(Request $request, Closure $next): mixed
    {
        $user = Auth::user();

        if ($user && $request->isMethod('GET')) {
            $path = trim($request->path(), '/');

            // If user visits the root '/admin'
            if ($path === 'admin') {
                $targetUrl = Role::getLandingPageForUser($user);
                $targetPath = trim(parse_url($targetUrl, PHP_URL_PATH) ?? '', '/');

                if ($targetPath !== '' && $targetPath !== 'admin') {
                    return redirect()->to($targetUrl);
                }
            }
        }

        return $next($request);
    }
}
