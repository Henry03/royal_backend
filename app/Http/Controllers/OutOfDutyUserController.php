<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Telegram\Bot\Laravel\Facades\Telegram;

class OutOfDutyUserController extends Controller
{
    public function indexByEmployee (Request $request) {
        $filter = $request->input('filter', 'created_at');
        $sort = $request->input('sort', 'desc');
        $search = $request->input('search');

        $data = DB::table('out_of_duty')
        ->where('id_staff', '=', Auth::user()->id_staff)
        ->where(function ($query) use ($search) {
            $query->where('destination', 'LIKE', '%'.$search.'%')
                ->orWhere('start_date', 'LIKE', '%'.$search.'%')
                ->orWhere('created_at', 'LIKE', '%'.$search.'%');
        })
        ->orderBy($filter, $sort)
        ->paginate(10);

        if($data->isEmpty()){
            return response()->json([
                'status' => false,
                'message' => 'Data not found',
                'data' => null
            ], 200);
        }

        return response()->json([
            'status' => true,
            'message' => 'Data Out of Duty',
            'data' => $data
        ], 200);
    }

    public function show ($id) {
        $data = DB::table('out_of_duty')
        ->where('id', $id)
        ->where('id_staff', Auth::user()->id_staff)
        ->first();

        return response()->json([
            'status' => true,
            'message' => 'Data Out of Duty',
            'data' => $data
        ], 200);
    }

    public function cancel ($id) {
        $data = DB::table('out_of_duty')
        ->where('id_staff', Auth::user()->id_staff)
        ->where('id', $id)
        ->where('status', 1)
        ->update(['status' => 0]);

        if($data){
            return response()->json([
                'status' => true,
                'message' => 'Out of Duty request has been canceled',
                'data' => null
            ], 200);
        }

        return response()->json([
            'status' => false,
            'message' => 'Out of Duty request on process',
            'data' => null
        ], 200);
    }

    public function store (Request $request) {
        $input = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'destination' => 'required',
            'purpose' => 'required',
        ]);

        $id = Auth::user()->id_staff;
        $staff = DB::table('hr_staff_info AS si')
            ->join('users AS u', 'si.FID', '=', 'u.id_staff')
            ->where('FID', $id)
            ->first();

        $input['id_staff'] = $id;
        
        $input['status'] = 1;   
        $input['track'] = 1;
        $input['created_at'] = now();
        $input['updated_at'] = now();

        if($staff->role == 1){
            $users = DB::table('users')
            ->join('hr_staff_info', 'users.id_staff', '=', 'hr_staff_info.FID')
            ->join('telegram_session', 'telegram_session.id_staff', '=', 'hr_staff_info.FID')
            ->select('telegram_session.id')
            ->where('users.id_unit', $staff->DEPT_NAME)
            ->where('telegram_session.status', 'Active')
            ->where(function($query) {
                $query->where('role', 2)
                    ->orWhere('role', 3)
                    ->orWhere('role', 4);
            })
            ->get();
        }else if($staff->role == 2){
            $users = DB::table('users')
            ->join('hr_staff_info', 'users.id_staff', '=', 'hr_staff_info.FID')
            ->join('telegram_session', 'telegram_session.id_staff', '=', 'hr_staff_info.FID')
            ->select('telegram_session.id')
            ->where('users.id_unit', $staff->DEPT_NAME)
            ->where('telegram_session.status', 'Active')
            ->where(function($query) {
                $query->where('role', 3)
                    ->orWhere('role', 4);
            })
            ->get();
        }else if($staff->role == 3){
            $users = DB::table('users')
            ->join('hr_staff_info', 'users.id_staff', '=', 'hr_staff_info.FID')
            ->join('telegram_session', 'telegram_session.id_staff', '=', 'hr_staff_info.FID')
            ->select('telegram_session.id')
            ->where('users.id_unit', $staff->DEPT_NAME)
            ->where('telegram_session.status', 'Active')
            ->where(function($query) {
                $query->where('role', 4);
            })
            ->get();
        }else if($staff->role == 4){
            $users = DB::table('users')
            ->join('hr_staff_info', 'users.id_staff', '=', 'hr_staff_info.FID')
            ->join('telegram_session', 'telegram_session.id_staff', '=', 'hr_staff_info.FID')
            ->select('telegram_session.id')
            ->where('users.id_unit', $staff->DEPT_NAME)
            ->where('telegram_session.status', 'Active')
            ->where(function($query) {
                $query->where('role', 5);
            })
            ->get();
        }
        
        if($users){
            $message = "<pre>";
            $message .= "<b>Out of Duty request from :</b>";
            $message .= "\nName        : " . $staff->Nama;
            $message .= "\nDestination : " . $input['destination'];
            $message .= "\nStart Date  : " . date('l, d F Y, H:i', strtotime($input['start_date']));
            $message .= "\nEnd Date    : " . date('l, d F Y, H:i', strtotime($input['end_date']));
            $message .= "\nPurpose     : " . $input['purpose'];
            $message .= "</pre>";

            foreach($users as $user){
                Telegram::sendMessage([
                    'chat_id' => $user->id,
                    'text' => $message,
                    'parse_mode' => 'HTML'
                ]);
            }
        }

        // $outOfDuty = DB::table('out_of_duty')->insert($input);

        return response()->json([
            'status' => true,
            'message' => 'Out of Duty request has been sent',
            'data' => $input
        ], 200);
    }

}
