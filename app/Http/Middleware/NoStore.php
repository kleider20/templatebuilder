<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class NoStore
{
    public function handle(Request $request, Closure $next): SymfonyResponse
    {
        $response = $next($request);

        if ($response instanceof Response) {
            $response = $response->toResponse($request);
        }

        $response->headers->set('Cache-Control', 'no-store');

        return $response;
    }
}
