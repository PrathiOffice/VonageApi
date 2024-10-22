<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ApiKeyMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
         // Define your API key (you can store this in your .env file)
         $apiKey = env('API_KEY', 'xgtjiKeeksubuowhfobPrathiuohouhfouhwbfuobjKeenubbugbhobhbiohbnjoiO79y9gbiT86igyvukt6t867tgiout97tguyi8t7igyvb87it97yg8i7frt97ty98yt8fyih8y98ty8gihuug');

         // Check if the X-API-KEY header is present and matches the predefined key
         if ($request->header('X-API-KEY') !== $apiKey) {
             return response()->json(['error' => 'Unauthorized'], 401);
         }
        return $next($request);
    }
}
