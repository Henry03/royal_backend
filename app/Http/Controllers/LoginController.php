<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Login;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Laravel\Facades\Telegram;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $input = $request->validate([
            'no_telp' => 'required|regex:/^08[1-9][0-9]{6,9}$/'
        ]);

        $data =  DB::table('hr_staff_info')
        ->select('*')
        ->where('Notelp', '=', $input['no_telp'])
        ->get();

        if($data->isEmpty()){
            return response()->json([
                'success' => false,
                'message' => 'Phone number not found, please check your phone number or contact the admin',
                'data' => null
            ], 422);
        }

        $id_staff = $data->first()->FID;
        $otp = rand(100000, 999999);
        $exp_date = date('Y-m-d H:i:s', strtotime('+30 minutes'));

        $login = new Login();
        $login->id_staff = $id_staff;
        $login->otp = $otp;
        $login->exp_date = $exp_date;
        $login->save();

        $users = DB::table('hr_staff_info')
        ->join('telegram_session', 'telegram_session.id_staff', '=', 'hr_staff_info.FID')
        ->select('telegram_session.id')
        ->where('telegram_session.status', 'Active')
        ->where('hr_staff_info.FID', $id_staff)
        ->get();

        if($users){
            $message = "Kode OTP anda adalah <b>$otp</b> dan akan kadaluarsa pada <b>$exp_date</b>";

            foreach($users as $user){
                Telegram::sendMessage([
                    'chat_id' => $user->id,
                    'text' => $message,
                    'parse_mode' => 'HTML'
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'OTP has been sent to your phone number',
            'data' => $login
        ]);
    }

    public function otp(Request $request)
    {
        $input = $request->validate([
            'no_telp' => 'required|regex:/^08[1-9][0-9]{6,9}$/',
            'otp' => 'required|numeric|digits:6'
        ]);

        $data =  DB::table('login_access as la')
        ->select('la.*', 'si.*')
        ->join('hr_staff_info as si', 'la.ID_STAFF', '=', 'si.FID')
        ->where('la.OTP', $input['otp'])
        ->where('si.Notelp', $input['no_telp'])
        ->get();

        if($data->isEmpty()){
            return response()->json([
                'success' => false,
                'message' => 'OTP is invalid',
                'data' => null
            ], 422);
        }
        $otp = $input['otp'];
        
        // DB::enableQueryLog();
        $login = Login::where('otp', $otp)->first();
        // dd(DB::getQueryLog());

        if($login && $login->exp_date > now()->toDateTimeString()){
            $login->token = Hash::make($input['otp']);
            $login->otp = null;
            $login->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Login successfully',
                'data' => $login
            ], 200);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'OTP is expired',
            'data' => null
        ], 422);
    }

    public function authCheck (Request $request)
    {
        $input = $request->validate([
            'token' => 'required'
        ]);

        $data =  DB::table('login_access')
        ->select('*')
        ->where('token', $input['token'])
        ->first();
        
        if(!$data){
            return response()->json([
                'success' => false,
                'message' => 'Token is invalid',
                'data' => null
            ], 401);
        }

        if($data->exp_date < now()){
            DB::table('login_access')
            ->where('token', $input['token'])
            ->update(['token' => null]);

            return response()->json([
                'success' => false,
                'message' => 'Token is expired',
                'data' => null
            ], 401);
        }



        $position = DB::table('hr_staff_info')
        ->select('*')
        ->where('FID', $data->id_staff)
        ->get();

        return response()->json([
            'success' => true,
            'message' => 'Token is valid',
            'data' => $position->first()
        ], 200);
    }

    public function logout (){
        $data = DB::table('login_access')
        ->where('otp', session('otp'))
        ->update(['otp' => null,'token' => null]);

        return response()->json([
            'success' => true,
            'message' => 'Logout successfully',
            'data' => $data
        ], 200);
    }
}
