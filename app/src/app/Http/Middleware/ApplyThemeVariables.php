<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class ApplyThemeVariables
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        View::share('darkmode', $user?->darkmode ?? session('darkmode', 'auto'));
        View::share('mainTheme', $user?->main_theme ?? 'neutral');
        View::share('secondaryTheme', $user?->secondary_theme ?? 'slate');
        View::share('accentTheme', $user?->accent_theme ?? 'sky');
        View::share('infoTheme', $user?->info_theme ?? 'blue');
        View::share('successTheme', $user?->success_theme ?? 'green');
        View::share('warningTheme', $user?->warning_theme ?? 'yellow');
        View::share('errorTheme', $user?->error_theme ?? 'red');

        return $next($request);
    }
}
