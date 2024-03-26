<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Admin;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class LoginAdminController extends Controller
{
    public function register (Request $request){
        $input = $request->validate([
            'id_staff' => 'required',
            'username' => 'required',
            'password' => 'required',
            'role' => 'required',
            'id_unit' => 'required'
        ]);

        $input['password'] = bcrypt($input['password']);
        $user = User::create($input);
        $token = $user->createToken('AuthToken')->plainTextToken;

        return response()->json([
            'status' => true,
            'message' => 'Register Success',
            'data' => $user,
            'token' => $token,
        ], 200);
    }

    public function login(Request $request)
    {
        $input = $request->validate([
            'username' => 'required',
            'password' => 'required'
        ]);

        if(Auth::attempt($input)){
            $user = Auth::user();

            $data = DB::table('hr_staff_info')
                ->select('FID', 'Nama', 'NamaUnit', 'role')
                ->join('users', 'hr_staff_info.FID', '=', 'users.id_staff')
                ->join('hr_unit', 'hr_staff_info.DEPT_NAME', '=', 'hr_unit.IdUnit')
                ->where('users.id', '=', $user->id)
                ->first();
            $token = $user->createToken('AuthToken', [$data->role])->plainTextToken;
            
            return response()->json([
                'status' => true,
                'message' => 'Login Success',
                'data' => Auth::user(),
                'token' => $token,
            ], 200);
        }

        return response()->json([
            'status' => false,
            'message' => 'Invalid Credentials',
        ], 401);
    }

    public function index()
    {
        return response()->json([
            'status' => true,
            'message' => 'Data Admin',
            'data' => User::all(),
        ], 200);
    }

    public function authCheck (Request $request)
    {
        $user = Auth::user();
        if($user){
            return response()->json([
                'status' => true,
                'message' => 'User is signed in',
                'data' => $user,
            ], 200);
        }else{
            return response()->json([
                'status' => false,
                'message' => 'User is not signed in',
            ], 401);
        }
    }

    public function logout () {
        $user = Auth::user();
        $user->tokens()->delete();

        return response()->json([
            'status' => true,
            'message' => 'Logout Success',
        ], 200);
    }
}
