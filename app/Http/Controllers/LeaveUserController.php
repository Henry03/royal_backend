<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Telegram\Bot\Laravel\Facades\Telegram;

class LeaveUserController extends Controller
{
    public function indexByEmployee (Request $request) {
        $filter = $request->input('filter', 'request_date');
        $sort = $request->input('sort', 'desc');

        $data = DB::table('leave_request AS lr')
            ->leftJoin('leave_request_dp as lrd', 'lrd.id_leave_request', '=', 'lr.id')
            ->leftJoin('leave_request_eo as lre', 'lre.id_leave_request', '=', 'lr.id')
            ->leftJoin('leave_request_al as lra', 'lra.id_leave_request', '=', 'lr.id')
            ->leftJoin('manager_on_duty as md', 'md.id', '=', 'lrd.id_mod')
            ->leftJoin('extra_off as eo', 'eo.id', '=', 'lre.id_eo')
            ->leftJoin('annual_leave as al', 'al.id', '=', 'lra.id_al')
            ->select('md.id_staff', 'lr.id', 'lr.request_date', 'lr.note', 'lr.status', 'lr.track')
            ->groupBy('md.id_staff', 'lr.id', 'lr.request_date', 'lr.note', 'lr.status', 'lr.track') // Group by the primary key
            ->where('md.id_staff', '=', Auth::user()->id_staff)
            ->orWhere('eo.id_staff', '=', Auth::user()->id_staff)
            ->orWhere('al.id_staff', '=', Auth::user()->id_staff)
            ->orderBy($filter, $sort)
            ->paginate(10);

        return response()->json([
            'status' => true,
            'message' => 'Data Leave Request',
            'data' => $data
        ], 200);
    }

    public function indexByEmployeeQuota () {
        $dp = DB::table('manager_on_duty as md')
            ->select('md.*')
            ->distinct()
            ->leftJoin('leave_request_dp as lrd', 'md.id', '=', 'lrd.id_mod')
            ->where('md.id_staff', Auth::user()->id_staff)
            ->where(function ($query) {
                $query->whereNull('lrd.approval')
                    ->orWhereNotIn('lrd.approval', [1, 2]);
            })
            ->whereNotExists(function ($subquery) {
                $subquery->select(DB::raw(1))
                    ->from('leave_request_dp as lrd_pending')
                    ->whereRaw('lrd_pending.id_mod = md.id')
                    ->where('lrd_pending.approval', 1);
            })
            ->where(function ($query) {
                $query->where('md.expire', '>', now())
                    ->orWhereNull('md.expire');
            })
            ->orderBy('md.date', 'ASC')
            ->get();

        $eo = DB::table('extra_off as eo')
        ->select('eo.*')
        ->distinct()
        ->leftJoin('leave_request_eo as lre', 'eo.id', '=', 'lre.id_eo')
        ->where('eo.id_staff', '=', Auth::user()->id_staff)
        ->where(function ($query) {
            $query->whereNull('lre.approval')
                ->orWhereNotIn('lre.approval', [1, 2]);
        })
        ->whereNotExists(function ($subquery) {
            $subquery->select(DB::raw(1))
                ->from('leave_request_eo as lrd_pending')
                ->whereRaw('lrd_pending.id_eo = eo.id')
                ->where('lrd_pending.approval', 1);
        })
        ->where(function ($query) {
            $query->where('eo.expire', '>', now())
                ->orWhereNull('eo.expire');
        })
        ->orderBy('eo.date', 'ASC')
        ->get();

        $al = DB::table('annual_leave as al')
        ->select('al.*')
        ->distinct()
        ->leftJoin('leave_request_al as lra', 'al.id', '=', 'lra.id_al')
        ->where('al.id_staff', '=', Auth::user()->id_staff)
        ->where(function ($query) {
            $query->whereNull('lra.approval')
                ->orWhereNotIn('lra.approval', [1, 2]);
        })
        ->whereNotExists(function ($subquery) {
            $subquery->select(DB::raw(1))
                ->from('leave_request_al as lra_pending')
                ->whereRaw('lra_pending.id_al = al.id')
                ->where('lra_pending.approval', 1);
        })
        ->where(function ($query) {
            $query->where('al.expire', '>', now())
                ->orWhereNull('al.expire');
        })
        ->orderBy('al.date', 'ASC')
        ->get();
        
        
        return response()->json([
            'status' => true,
            'message' => 'Data Day Payment',
            'dp' => $dp,
            'eo' => $eo,
            'al' => $al
        ], 200);
    }

    public function show($id) {
        
        $leave = DB::table('leave_request as lr')
            ->select('lr.*')
            ->where('lr.id', $id)
            ->first();

        $dp = DB::table('leave_request as lr')
            ->join('leave_request_dp as lrd', 'lr.id', '=', 'lrd.id_leave_request')
            ->join('manager_on_duty as md', 'lrd.id_mod', '=', 'md.id')
            ->select('lrd.id', 'lrd.date as date_replace', 'lrd.approval', 'md.date')
            ->where('lr.id', $id)
            ->get();

        $eo = DB::table('leave_request as lr')
            ->join('leave_request_eo as lre', 'lr.id', '=', 'lre.id_leave_request')
            ->join('extra_off as eo', 'lre.id_eo', '=', 'eo.id')
            ->select('lre.id','lre.date as date_replace', 'lre.approval', 'eo.date')
            ->where('lr.id', $id)
            ->get();

        $al = DB::table('leave_request as lr')
            ->join('leave_request_al as lra', 'lr.id', '=', 'lra.id_leave_request')
            ->join('annual_leave as al', 'lra.id_al', '=', 'al.id')
            ->select('lra.id', 'lra.date as date_replace', 'lra.approval', 'al.date')
            ->where('lr.id', $id)
            ->get();

        if ($dp) {
            return response()->json([
                'status' => true,
                'message' => 'Data Leave Permit',
                'dp' => $dp,
                'eo' => $eo,
                'al' => $al,
                'data' => $leave
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Data Leave Permit tidak ditemukan',
                'data' => $id
            ], 404);
        }
    }

    public function cancel ($id) {
        $staffId = Auth::user()->id_staff;
        $data = DB::table('leave_request as lr')
        ->leftJoin('leave_request_dp as lrd', 'lrd.id_leave_request', '=', 'lr.id')
        ->leftJoin('leave_request_eo as lre', 'lre.id_leave_request', '=', 'lr.id')
        ->leftJoin('leave_request_al as lra', 'lra.id_leave_request', '=', 'lr.id')
        ->leftJoin('manager_on_duty as md', 'md.id', '=', 'lrd.id_mod')
        ->leftJoin('extra_off as eo', 'eo.id', '=', 'lre.id_eo')
        ->leftJoin('annual_leave as al', 'al.id', '=', 'lra.id_al')
        ->where('lr.id', $id)
        ->where('lr.track', 1)
        ->where(function ($query) use ($staffId) {
            $query->where('md.id_staff', $staffId)
                ->orWhere('eo.id_staff', $staffId)
                ->orWhere('al.id_staff', $staffId);
        })
        ->select('lr.*', 'lrd.*', 'lre.*', 'lra.*', 'md.*', 'eo.*', 'al.*')
        ->update(['lr.status' => 0, 'lre.approval' => 0, 'lrd.approval' => 0, 'lra.approval' => 0]);

        if($data){
            return response()->json([
                'status' => true,
                'message' => 'Leave request has been canceled',
                'data' => null
            ], 200);
        }

        return response()->json([
            'status' => false,
            'message' => 'Leave Request data not found',
            'data' => null
        ], 404);
    }

    public function store(Request $request) {
        $request->validate([
            'inputDp' => 'array',
            'inputDp.*.id' => 'required',
            'inputDp.*.date' => 'required|date',
            'inputEo' => 'array',
            'inputAl' => 'array',
            'inputAl.*.id' => 'required|string',
            'inputAl.*.date' => 'required|date',
            'note' => 'string|required'
        ],[
            'inputDp.required' => 'Day Payment is required.',
            'inputDp.*.id.required' => 'Select a Day Payment option.',
            'inputDp.*.date.required' => 'Day Payment Date is required.',
            'inputDp.*.date.date' => 'Invalid Date format.',
            'inputEo.required' => 'Extra Off is required.',
            'inputEo.*.id.required' => 'Select an Extra Off option.',
            'inputEo.*.date.required' => 'Extra Off Date is required.',
            'inputEo.*.date.date' => 'Invalid Date format.',
            'inputAl.required' => 'Annual Leave is required.',
            'inputAl.*.id.required' => 'Select an Annual Leave option.',
            'inputAl.*.date.required' => 'Annual Leave Date is required.',
            'inputAl.*.date.date' => 'Invalid Date format.',
            'note.required' => 'Note is required.',
            'note.string' => 'Note must be a string.'
            
        ]);

        $input = $request->all();
        $now = now();

        if($input['inputDp'] == null && $input['inputEo'] == null && $input['inputAl'] == null){
            return response()->json([
                'status' => false,
                'message' => 'Please choose at least 1 type of leave',
                'data' => $input
            ], 500);
        }

        DB::table('leave_request')->insert([
            'request_date' => $now,
            'status' => 1,
            'track' => 1,
            'outstanding_dp' => 0,
            'outstanding_eo' => 0,
            'outstanding_al' => 0,
            'note' => $input['note']
        ]);

        $leave_request = DB::table('leave_request')
            ->where('request_date', $now)
            ->first();

        $employee = DB::table('hr_staff_info')
            ->join('users', 'users.id_staff', '=', 'hr_staff_info.FID')
            ->select('DEPT_NAME as id_department', 'Nama as name', 'JABATAN as position', 'users.role')
            ->where('Fid', Auth::user()->id_staff)
            ->first();

        $department = $employee->id_department;
        if($department == 'a000000001'){
            $department_name = 'FO';
        }else if($department == 'a000000002'){
            $department_name = 'HK';
        }else if($department == 'a000000003'){
            $department_name = 'FS';
        }else if($department == 'a000000004'){
            $department_name = 'FBP';
        }else if($department == 'a000000005'){
            $department_name = 'SM';
        }else if($department == 'a000000006'){
            $department_name = 'ACCT';
        }else if($department == 'a000000007'){
            $department_name = 'ENG';
        }else if($department == 'a000000008'){
            $department_name = 'HRD';
        }else if($department == 'a000000009'){
            $department_name = 'LAUNDRY';
        }else if($department == 'a000000010'){
            $department_name = 'OSS';
        }else if($department == 'a000000011'){
            $department_name = 'OSG';
        }else if($department == 'a000000013'){
            $department_name = 'EO';
        }else if($department == 'a000000014'){
            $department_name = 'RA';
        }else if($department == 'a000000015'){
            $department_name = 'OSS';
        }else if($department == 'a000000016'){
            $department_name = 'ROOM';
        }else if($department == 'a000000017'){
            $department_name = 'DCO';
        }else if($department == 'a000000019'){
            $department_name = 'FO';
        }else if($department == 'a000000020'){
            $department_name = 'HK';
        }else if($department == 'a000000021'){
            $department_name = 'FBS';
        }else if($department == 'a000000022'){
            $department_name = 'FBP';
        }else if($department == 'a000000023'){
            $department_name = 'HR';
        }else if($department == 'a000000024'){
            $department_name = 'ACCT';
        }else if($department == 'a000000025'){
            $department_name = 'ENG';
        }else{
            $department_name = 'UNDEFINED';
        }


        DB::table('leave_request')
            ->where('request_date', $now)
            ->update([
                'reference_number' => $leave_request->id.'/'.$department_name.'/'.date('m').'/'.date('Y')
            ]);

        $leave = DB::table('leave_request')
            ->where('request_date', $now)
            ->first();

        if($input['inputDp']){
            foreach ($input['inputDp'] as $key => $value) {
                $dpDate[$key]['id_leave_request'] = $leave->id;
                $dpDate[$key]['id_mod'] = $value['id'];
                $dpDate[$key]['date'] = $value['date'];
                $dpDate[$key]['approval'] = 1;
            }
            $status = DB::table('leave_request_dp')->insert($dpDate);
        }

        if($input['inputEo']){
            foreach ($input['inputEo'] as $key => $value) {
                $eoDate[$key]['id_leave_request'] = $leave->id;
                $eoDate[$key]['id_eo'] = $value['id'];
                $eoDate[$key]['date'] = $value['date'];
                $eoDate[$key]['approval'] = 1;
            }
            $status = DB::table('leave_request_eo')->insert($eoDate);
        }

        if($input['inputAl']){
            foreach ($input['inputAl'] as $key => $value) {
                $alDate[$key]['id_leave_request'] = $leave->id;
                $alDate[$key]['id_al'] = $value['id'];
                $alDate[$key]['date'] = $value['date'];
                $alDate[$key]['approval'] = 1;
            }
            $status = DB::table('leave_request_al')->insert($alDate);
        }

        if($employee->role == 1){
            $users = DB::table('users')
            ->join('hr_staff_info', 'users.id_staff', '=', 'hr_staff_info.FID')
            ->join('telegram_session', 'telegram_session.id_staff', '=', 'hr_staff_info.FID')
            ->select('telegram_session.id')
            ->where('users.id_unit', $employee->id_department)
            ->where('telegram_session.status', 'Active')
            ->where(function($query) {
                $query->where('role', 2)
                    ->orWhere('role', 3)
                    ->orWhere('role', 4);
            })
            ->get();
        }else if($employee->role == 2){
            $users = DB::table('users')
            ->join('hr_staff_info', 'users.id_staff', '=', 'hr_staff_info.FID')
            ->join('telegram_session', 'telegram_session.id_staff', '=', 'hr_staff_info.FID')
            ->select('telegram_session.id')
            ->where('users.id_unit', $employee->DEPT_NAME)
            ->where('telegram_session.status', 'Active')
            ->where(function($query) {
                $query->where('role', 3)
                    ->orWhere('role', 4);
            })
            ->get();
        }else if($employee->role == 3){
            $users = DB::table('users')
            ->join('hr_staff_info', 'users.id_staff', '=', 'hr_staff_info.FID')
            ->join('telegram_session', 'telegram_session.id_staff', '=', 'hr_staff_info.FID')
            ->select('telegram_session.id')
            ->where('users.id_unit', $employee->DEPT_NAME)
            ->where('telegram_session.status', 'Active')
            ->where(function($query) {
                $query->where('role', 4);
            })
            ->get();
        }else if($employee->role == 4){
            $users = DB::table('users')
            ->join('hr_staff_info', 'users.id_staff', '=', 'hr_staff_info.FID')
            ->join('telegram_session', 'telegram_session.id_staff', '=', 'hr_staff_info.FID')
            ->select('telegram_session.id')
            ->where('users.id_unit', $employee->DEPT_NAME)
            ->where('telegram_session.status', 'Active')
            ->where(function($query) {
                $query->where('role', 5);
            })
            ->get();
        }

        if($users){
            $message = "<pre>";
            $message .= "<b>Leave permit request from :</b>";
            $message .= "\nName        : " . $employee->name;
            $message .= "\nPosition    : " . $employee->position;
            
            if($input['inputDp']){
                $message .= "\n\nDay Payment : ";
                foreach ($input['inputDp'] as $key => $value) {
                    $message .= "\n  ".($key+1).". Date     : " . date('l, d F Y', strtotime($value['date']));
                }
            }
            if($input['inputEo']){
                $message .= "\n\nExtra Off : ";
                foreach ($input['inputEo'] as $key => $value) {
                    $message .= "\n  ".($key+1).". Date     : " . date('l, d F Y', strtotime($value['date']));
                }
            }
            if($input['inputAl']){
                $message .= "\n\nAnnual Leave : ";
                foreach ($input['inputAl'] as $key => $value) {
                    $message .= "\n  ".($key+1).". Date     : " . date('l, d F Y', strtotime($value['date']));
                }
            }

            $message .= "\n\nNote     : " . $input['note'];
            $message .= "</pre>";

            foreach($users as $user){
                Telegram::sendMessage([
                    'chat_id' => $user->id,
                    'text' => $message,
                    'parse_mode' => 'HTML'
                ]);
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'Data Leave Permit berhasil ditambahkan',
            'data' => $status
        ], 200);
    }
}
