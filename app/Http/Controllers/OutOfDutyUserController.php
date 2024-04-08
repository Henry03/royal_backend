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
        return response()->json([
            'status' => true,
            'message' => 'Data Out of Duty',
            'data' => $data,
            'id' => Auth::user()->id_staff
        ], 200);

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
        ->leftJoin(DB::raw('(SELECT users.id_staff, users.deleted_at FROM users WHERE users.deleted_at IS NULL) AS us'), function ($join) {
            $join->on('out_of_duty.id_staff', '=', 'us.id_staff');
        })
        ->select('out_of_duty.*', 'us.*')
        ->where('out_of_duty.id', $id)
        ->first();

        $step = DB::table('out_of_duty as od')
            ->select(DB::raw("
                CASE
                    WHEN odu.track = 1 AND odu.status = 1 THEN 'Approved by Admin'
                    WHEN odu.track = 2 AND odu.status = 1 THEN 'Approved by Chief'
                    WHEN odu.track = 3 AND odu.status = 1 THEN 'Approved Asst. HOD'
                    WHEN odu.track = 4 AND odu.status = 1 THEN 'Approved by HOD'
                    WHEN odu.track = 5 AND odu.status = 1 THEN 'Approved by GM'
                    WHEN odu.track = 6 AND odu.status = 1 THEN 'Acknowledge by HRD'
                    WHEN odu.track = 1 AND odu.status = 0 THEN 'Rejected by Admin'
                    WHEN odu.track = 2 AND odu.status = 0 THEN 'Rejected by Chief'
                    WHEN odu.track = 3 AND odu.status = 0 THEN 'Rejected Asst. HOD'
                    WHEN odu.track = 4 AND odu.status = 0 THEN 'Rejected by HOD'
                    WHEN odu.track = 5 AND odu.status = 0 THEN 'Rejected by GM'
                    WHEN odu.track = 6 AND odu.status = 0 THEN 'Rejected by HRD'
                END as approval_status"), 'u.role', 'odu.track', 'odu.status', 'si.name', 'odu.created_at')
            ->join('out_of_duty_update as odu', 'od.id', '=', 'odu.id_out_of_duty')
            ->join('users as u', 'u.id', '=', 'odu.id_user')
            ->join('staff AS si', 'si.id', '=', 'u.id_staff')
            ->where('od.id', $id)
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Data Out of Duty',
            'data' => $data,
            'step' => $step
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
            ->where('u.deleted_at', null)
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
            ->where('users.deleted_at', null)
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
            ->where('users.deleted_at', null)
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
            ->where('users.deleted_at', null)
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
            ->where('users.deleted_at', null)
            ->get();
        }else if($staff->role == 5){
            $users = DB::table('users')
            ->join('hr_staff_info', 'users.id_staff', '=', 'hr_staff_info.FID')
            ->join('telegram_session', 'telegram_session.id_staff', '=', 'hr_staff_info.FID')
            ->select('telegram_session.id')
            ->where('telegram_session.status', 'Active')
            ->where(function($query) {
                $query->where('role', 6);
            })
            ->where('users.deleted_at', null)
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

        $outOfDuty = DB::table('out_of_duty')->insert($input);

        return response()->json([
            'status' => true,
            'message' => 'Out of Duty request has been sent',
            'data' => $input
        ], 200);
    }

}
