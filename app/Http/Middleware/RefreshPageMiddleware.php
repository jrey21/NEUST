<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RefreshPageMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Implement the logic for the middleware
        $response = $next($request);
        
        if ($response instanceof \Illuminate\Http\RedirectResponse) {
            $response->header('Cache-Control', 'no-cache, no-store, must-revalidate');
            $response->header('Pragma', 'no-cache');
            $response->header('Expires', '0');
        }

        return $response;
    }
}