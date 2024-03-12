<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\DB;

class EmployeeTokenCheckMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        // Get the token from the Authorization header
        $token = $request->header('Authorization');

        if ($this->isValidToken($token, $request)) {
            // Token is valid, continue with the request
            return $next($request);
        }

        // Token is not valid, return unauthorized response
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    private function isValidToken($token, $request)
    {
        // Implement your logic to check the token against the database
        $loginAccess = DB::table('login_access')
            ->select('exp_date', 'id_staff', 'otp')
            ->where('token', '=', $token)
            ->first();

        if ($loginAccess && $loginAccess->exp_date > now()) {
            // Token is valid, store id_staff in the request for later use
            session(['id_staff' => $loginAccess->id_staff]);
            session(['otp' => $loginAccess->otp]);

            return true;
        }

        return false;
    }
}
