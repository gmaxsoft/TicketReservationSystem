<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureConcertStaffAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->session()->get('concert_staff_authenticated')) {
            return redirect()->route('concert.staff.login');
        }

        return $next($request);
    }
}
