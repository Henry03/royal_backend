<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\LaravelPdf\Facades\Pdf;
use Spatie\LaravelPdf\Enums\Unit;


class LeavePermitController extends Controller
{
    public function indexbyHRD (Request $request) {
        $filter = $request->input('filter', 'request_date');
        $sort = $request->input('sort', 'desc');

        $data = DB::table('leave_request AS lr')
            ->leftJoin('leave_request_dp as lrd', 'lrd.id_leave_request', '=', 'lr.id')
            ->leftJoin('leave_request_eo as lre', 'lre.id_leave_request', '=', 'lr.id')
            ->leftJoin('leave_request_al as lra', 'lra.id_leave_request', '=', 'lr.id')
            ->leftJoin('manager_on_duty as md', 'md.id', '=', 'lrd.id_mod')
            ->leftJoin('extra_off as eo', 'eo.id', '=', 'lre.id_eo')
            ->leftJoin('annual_leave as al', 'al.id', '=', 'lra.id_al')
            ->leftJoin('hr_staff_info as md_hr', 'md_hr.Fid', '=', 'md.id_staff')
            ->leftJoin('hr_staff_info as eo_hr', 'eo_hr.Fid', '=', 'eo.id_staff')
            ->leftJoin('hr_staff_info as al_hr', 'al_hr.Fid', '=', 'al.id_staff')
            ->select(
                'md.id_staff',
                'lr.id',
                'lr.request_date',
                'lr.note',
                'lr.status',
                'lr.track',
                DB::raw('CASE
                    WHEN md.id_staff IS NOT null THEN md_hr.Nama
                    WHEN eo.id_staff IS NOT null THEN eo_hr.Nama
                    WHEN al.id_staff IS NOT null THEN al_hr.Nama
                END AS name')
            )
            ->where(function ($query){
                $query
                    ->where(function ($query){
                        $query->where('track', 2)
                            ->where('status', 1);
                    })
                    ->orWhere(function ($query){
                        $query->where('track', 3)
                            ->where('status', 1);
                    })
                    ->orWhere(function ($query){
                        $query->where('track', 4)
                            ->where('status', 1);
                    })
                    ->orWhere(function ($query) {
                        $query->where('track', 5)
                            ->where('status', 1);
                    })
                    ->orWhere('track', 6);
            })
            ->groupBy('lr.id', 'lr.request_date', 'lr.note', 'lr.status', 'lr.track', 'md.id_staff', 'eo.id_staff', 'al.id_staff', 'md_hr.Nama', 'eo_hr.Nama', 'al_hr.Nama')
            ->orderBy($filter, $sort)
            ->paginate(10);

        return response()->json([
            'status' => true,
            'message' => 'Data Leave Request',
            'data' => $data
        ], 200);
    }

    public function indexbyDepartment (Request $request) {
        $filter = $request->input('filter', 'request_date');
        $sort = $request->input('sort', 'desc');

        $id = Auth::user()->id_staff;

        $user = DB::table('users')
            ->join('hr_staff_info', 'users.id_staff', '=', 'hr_staff_info.FID')
            ->where('id_staff', '=', $id)
            ->first();

        $data = DB::table('leave_request AS lr')
            ->leftJoin('leave_request_dp as lrd', 'lrd.id_leave_request', '=', 'lr.id')
            ->leftJoin('leave_request_eo as lre', 'lre.id_leave_request', '=', 'lr.id')
            ->leftJoin('leave_request_al as lra', 'lra.id_leave_request', '=', 'lr.id')
            ->leftJoin('manager_on_duty as md', 'md.id', '=', 'lrd.id_mod')
            ->leftJoin('extra_off as eo', 'eo.id', '=', 'lre.id_eo')
            ->leftJoin('annual_leave as al', 'al.id', '=', 'lra.id_al')
            ->leftJoin('hr_staff_info as md_hr', 'md_hr.Fid', '=', 'md.id_staff')
            ->leftJoin('hr_staff_info as eo_hr', 'eo_hr.Fid', '=', 'eo.id_staff')
            ->leftJoin('hr_staff_info as al_hr', 'al_hr.Fid', '=', 'al.id_staff')
            ->select(
                'lr.id',
                'lr.request_date',
                'lr.note',
                'lr.status',
                'lr.track',
                DB::raw('CASE
                    WHEN md.id_staff IS NOT null THEN md_hr.Nama
                    WHEN eo.id_staff IS NOT null THEN eo_hr.Nama
                    WHEN al.id_staff IS NOT null THEN al_hr.Nama
                END AS name')
            )
            ->where(function ($query) use ($user) {
                $query->where('md_hr.DEPT_NAME', '=', $user->DEPT_NAME)
                    ->orWhere('eo_hr.DEPT_NAME', '=', $user->DEPT_NAME)
                    ->orWhere('al_hr.DEPT_NAME', '=', $user->DEPT_NAME);
            })
            ->where(function ($query){
                $query
                    ->where(function ($query){
                        $query->where('track', 1)
                            ->where('status', 1);
                    })
                    ->orWhere('track', 2    )
                    ->orWhere('track', 3)
                    ->orWhere('track', 4)
                    ->orWhere('track', 5)
                    ->orWhere('track', 6);
            })
            ->groupBy('lr.id', 'lr.request_date', 'lr.note', 'lr.status', 'lr.track', 'md.id_staff', 'eo.id_staff', 'al.id_staff', 'md_hr.Nama', 'eo_hr.Nama', 'al_hr.Nama')
            ->orderBy($filter, $sort)
            ->paginate(10);

        return response()->json([
            'status' => true,
            'message' => 'Data Leave Request',
            'data' => $data
        ], 200);
    }

    public function indexbyDepartmentApproved (Request $request) {

        $id = Auth::user()->id_staff;

        $user = DB::table('users')
            ->join('hr_staff_info', 'users.id_staff', '=', 'hr_staff_info.FID')
            ->where('id_staff', '=', $id)
            ->first();

        $dp = DB::table('leave_request_dp as lrd')
            ->join('manager_on_duty as md', 'lrd.id_mod', '=', 'md.id')
            ->join('hr_staff_info as md_hr', 'md.id_staff', '=', 'md_hr.Fid')
            ->select('lrd.date as replace_date', 'md.date', 'md_hr.Nama')
            ->where('lrd.approval', 2)
            ->get();
        $eo = DB::table('leave_request_eo as lre')
            ->join('extra_off as eo', 'lre.id_eo', '=', 'eo.id')
            ->join('hr_staff_info as eo_hr', 'eo.id_staff', '=', 'eo_hr.Fid')
            ->select('lre.date as replace_date', 'eo.date', 'eo_hr.Nama')
            ->where('lre.approval', 2)
            ->get();
        $al = DB::table('leave_request_al as lra')
            ->join('annual_leave as al', 'lra.id_al', '=', 'al.id')
            ->join('hr_staff_info as al_hr', 'al.id_staff', '=', 'al_hr.Fid')
            ->select('lra.date as replace_date', 'al.date', 'al_hr.Nama')
            ->where('lra.approval', 2)
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Data Leave Request',
            'dp' => $dp,
            'eo' => $eo,
            'al' => $al
        ], 200);
    }

    public function indexbyEmployee (Request $request) {
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
            ->where('md.id_staff', '=', session('id_staff'))
            ->orWhere('eo.id_staff', '=', session('id_staff'))
            ->orWhere('al.id_staff', '=', session('id_staff'))
            ->orderBy($filter, $sort)
            ->paginate(10);

        return response()->json([
            'status' => true,
            'message' => 'Data Leave Request',
            'data' => $data
        ], 200);
    }

    public function indexEmployeeQuota () {
        $dp = DB::table('manager_on_duty as md')
            ->select('md.*')
            ->distinct()
            ->leftJoin('leave_request_dp as lrd', 'md.id', '=', 'lrd.id_mod')
            ->where('md.id_staff', session('id_staff'))
            ->where(function ($query) {
                $query->whereNull('lrd.approval')
                    ->orWhereNotIn('lrd.approval', [1, 2, 3]);
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
        ->where('eo.id_staff', '=', session('id_staff'))
        ->where(function ($query) {
            $query->whereNull('lre.approval')
                ->orWhereNotIn('lre.approval', [1, 2, 3]);
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
        ->where('al.id_staff', '=', session('id_staff'))
        ->where(function ($query) {
            $query->whereNull('lra.approval')
                ->orWhereNotIn('lra.approval', [1, 2, 3]);
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

    public function store(Request $request) {
        $request->validate([
            'inputDp' => 'array',
            'inputDp.*.id' => 'required',
            'inputDp.*.date' => 'required|date',
            'inputEo' => 'array',
            'inputAl' => 'array',
            'inputAl.*.id' => 'required|string',
            'inputAl.*.date' => 'required|date',
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
            'outstanding_al' => 0
        ]);

        $leave_request = DB::table('leave_request')
            ->where('request_date', $now)
            ->first();

        $employee = DB::table('hr_staff_info')
            ->select('DEPT_NAME as id_department')
            ->where('Fid', session('id_staff'))
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

        return response()->json([
            'status' => true,
            'message' => 'Data Leave Permit berhasil ditambahkan',
            'data' => $status
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
        $staffId = session('id_staff');
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

    public function approve (Request $request, $id) {
        $ids = $request->input('idStaff');

        $outstandingDp =  DB::table('manager_on_duty AS md')
            ->select('md.id')
            ->leftJoin('leave_request_dp AS lrd', 'md.id', '=', 'lrd.id_mod')
            ->where('md.id_staff', $ids)
            ->where(function ($query) {
                $query->where('md.expire', '>=', now())
                    ->orWhereNull('md.expire');
            })
            ->where(function ($query) {
                $query->whereNull('lrd.id_mod')
                    ->orWhere('lrd.approval', '!=', 2);
            })
            ->groupBy('md.id')
            ->distinct()
            ->get()
            ->count();

        $outstandingEo =  DB::table('extra_off AS eo')
            ->select('eo.id')
            ->leftJoin('leave_request_eo AS lre', 'eo.id', '=', 'lre.id_eo')
            ->where('eo.id_staff', $ids)
            ->where(function ($query) {
                $query->where('eo.expire', '>=', now())
                    ->orWhereNull('eo.expire');
            })
            ->where(function ($query) {
                $query->whereNull('lre.id_eo')
                    ->orWhere('lre.approval', '!=', 2);
            })
            ->groupBy('eo.id')
            ->distinct()
            ->get()
            ->count();

        $outstandingAl =  DB::table('annual_leave AS al')
            ->select('al.id')
            ->leftJoin('leave_request_al AS lra', 'al.id', '=', 'lra.id_al')
            ->where('al.id_staff', $ids)
            ->where(function ($query) {
                $query->where('al.expire', '>=', now())
                    ->orWhereNull('al.expire');
            })
            ->where(function ($query) {
                $query->whereNull('lra.id_al')
                    ->orWhere('lra.approval', '!=', 2);
            })
            ->groupBy('al.id')
            ->distinct()
            ->get()
            ->count();

        if(Auth::user()->role == 2){
            $track = 2;
        }else if(Auth::user()->role == 3){
            $track = 3;
        }else if(Auth::user()->role == 4){
            $track = 4;
        }else if(Auth::user()->role == 5){
            $track = 5;
        }else if(Auth::user()->role == 6){
            $track = 6;
            $approval = 2;

            DB::table('leave_request as lr')
            ->leftJoin('leave_request_dp as lrd', 'lrd.id_leave_request', '=', 'lr.id')
            ->where('lr.id', $id)
            ->where('lrd.approval', 1)
            ->update(['lrd.approval' => $approval]);

            DB::table('leave_request as lr')
            ->leftJoin('leave_request_eo as lre', 'lre.id_leave_request', '=', 'lr.id')
            ->where('lr.id', $id)
            ->where('lre.approval', 1)
            ->update(['lre.approval' => $approval]);

            DB::table('leave_request as lr')
            ->leftJoin('leave_request_al as lra', 'lra.id_leave_request', '=', 'lr.id')
            ->where('lr.id', $id)
            ->where('lra.approval', 1)
            ->update(['lra.approval' => $approval]);
        }

        DB::table('leave_request')
            ->where('id', $id)
            ->update([
                'outstanding_dp' => $outstandingDp,
                'outstanding_eo' => $outstandingEo,
                'outstanding_al' => $outstandingAl
            ]);

        DB::table('leave_request_update')
            ->insert([
                'id_leave_request' => $id,
                'id_user' => Auth::user()->id,
                'track' => $track,
                'created_at' => now()
            ]);

        $data = DB::table('leave_request')
        ->where('id', $id)
        ->update(['track' => $track, 'outstanding_dp' => $outstandingDp, 'outstanding_eo' => $outstandingEo, 'outstanding_al' => $outstandingAl]);

        if($data){
            return response()->json([
                'status' => true,
                'message' => 'Leave request has been approved',
                'data' => null
            ], 200);
        }

        return response()->json([
            'status' => false,
            'message' => 'Leave request failed to approve',
            'data' => null
        ], 200);
        
    }

    public function rejectDp ($id) {
        $data = DB::table('leave_request_dp')
            ->where('id', $id)
            ->update(['approval' => 3]);

        if($data){
            return response()->json([
                'status' => true,
                'message' => 'Day Payment has been rejected',
                'data' => null
            ], 200);
        }

        return response()->json([
            'status' => false,
            'message' => 'Day Payment data not found',
            'data' => null
        ], 404);
    }

    public function rejectEo ($id) {
        $data = DB::table('leave_request_eo')
            ->where('id', $id)
            ->update(['approval' => 3]);

        if($data){
            return response()->json([
                'status' => true,
                'message' => 'Extra Off has been rejected',
                'data' => null
            ], 200);
        }

        return response()->json([
            'status' => false,
            'message' => 'Extra Off data not found',
            'data' => null
        ], 404);
    }

    public function rejectAl ($id) {
        $data = DB::table('leave_request_al')
            ->where('id', $id)
            ->update(['approval' => 3]);

        if($data){
            return response()->json([
                'status' => true,
                'message' => 'Annual Leave has been rejected',
                'data' => null
            ], 200);
        }

        return response()->json([
            'status' => false,
            'message' => 'Annual Leave data not found',
            'data' => null
        ], 404);
    }

    public function reject ($id) {
        if(Auth::user()->role == 2){
            $track = 2;
        }else if(Auth::user()->role == 3){
            $track = 3;
        }else if(Auth::user()->role == 4){
            $track = 4;
        }else if(Auth::user()->role == 5){
            $track = 5;
        }else if(Auth::user()->role == 6){
            $track = 6;
        }

        $user = DB::table('leave_request_update')
            ->insert([
                'id_leave_request' => $id,
                'id_user' => Auth::user()->id,
                'track' => $track,
                'created_at' => now()
            ]);

        $data = DB::table('leave_request as lr')
            ->leftJoin('leave_request_dp as lrd', 'lrd.id_leave_request', '=', 'lr.id')
            ->leftJoin('leave_request_eo as lre', 'lre.id_leave_request', '=', 'lr.id')
            ->leftJoin('leave_request_al as lra', 'lra.id_leave_request', '=', 'lr.id')
            ->where('lr.id', $id)
            ->select('lr.*', 'lrd.*', 'lre.*', 'lra.*')
            ->update(['lr.status' => 0, 'lr.track' => $track, 'lre.approval' => 3, 'lrd.approval' => 3, 'lra.approval' => 3]);

        if($data){
            return response()->json([
                'status' => true,
                'message' => 'Leave request has been rejected',
                'data' => null
            ], 200);
        }

        return response()->json([
            'status' => false,
            'message' => 'Leave Request data not found',
            'data' => null
        ], 404);
    }

    public function download(Request $request){
        $id = $request->input('id');
        $ids = $request->input('ids');

        $leave = DB::table('leave_request as lr')
            ->select('lr.*')
            ->where('lr.id', $id)
            ->first();

        $leave->request_date = date('l, d F Y', strtotime($leave->request_date));
        $leave->created_at = date('d-m-Y', strtotime($leave->request_date));
        
        $dp = DB::table('leave_request as lr')
            ->join('leave_request_dp as lrd', 'lr.id', '=', 'lrd.id_leave_request')
            ->join('manager_on_duty as md', 'lrd.id_mod', '=', 'md.id')
            ->select('lrd.id', 'lrd.date as date_replace', 'lrd.approval', 'md.date')
            ->where('lr.id', $id)
            ->where('md.id_staff', $ids)
            ->where('lrd.approval', 2)
            ->get();

        $dpAll = DB::table('manager_on_duty as md')
            ->join('leave_request_dp as lrd', 'md.id', '=', 'lrd.id_mod')
            ->select('md.*')
            ->where('lrd.id_leave_request', $id)
            ->get();

        foreach($dp as $item){
            $item->date = date('l, d F Y', strtotime($item->date));
            $item->date_replace = date('l, d F Y', strtotime($item->date_replace));
        }

        $eo = DB::table('leave_request as lr')
            ->join('leave_request_eo as lre', 'lr.id', '=', 'lre.id_leave_request')
            ->join('extra_off as eo', 'lre.id_eo', '=', 'eo.id')
            ->select('lre.id','lre.date as date_replace', 'lre.approval', 'eo.date')
            ->where('lr.id', $id)
            ->where('eo.id_staff', $ids)
            ->where('lre.approval', 2)
            ->get();

        $eoAll = DB::table('extra_off as eo')
            ->join('leave_request_eo as lre', 'eo.id', '=', 'lre.id_eo')
            ->select('eo.*')
            ->where('lre.id_leave_request', $id)
            ->get();

        foreach($eo as $item){
            $item->date = date('l, d F Y', strtotime($item->date));
            $item->date_replace = date('l, d F Y', strtotime($item->date_replace));
        }

        $al = DB::table('leave_request as lr')
            ->join('leave_request_al as lra', 'lr.id', '=', 'lra.id_leave_request')
            ->join('annual_leave as al', 'lra.id_al', '=', 'al.id')
            ->select('lra.id', 'lra.date as date_replace', 'lra.approval', 'al.date')
            ->where('lr.id', $id)
            ->where('al.id_staff', $ids)
            ->where('lra.approval', 2)
            ->get();

        $alAll = DB::table('annual_leave as al')
            ->join('leave_request_al as lra', 'al.id', '=', 'lra.id_al')
            ->select('al.*')
            ->where('lra.id_leave_request', $id)
            ->get();

        foreach($al as $item){
            $item->date = date('l, d F Y', strtotime($item->date));
            $item->date_replace = date('l, d F Y', strtotime($item->date_replace));
        }

        $staff = DB::table('hr_staff_info as si')
            ->join('hr_unit as u', 'si.DEPT_NAME', '=', 'u.IdUnit')
            ->select('si.Nama as name', 'si.Jabatan as position', 'u.Namaunit as unit')
            ->where('si.FID', $ids)
            ->first();

            $staff->position = mb_convert_case($staff->position, MB_CASE_TITLE, 'UTF-8');
            $staff->unit = mb_convert_case($staff->unit, MB_CASE_TITLE, 'UTF-8');

        $users = DB::table('leave_request_update as lru')
            ->join('users as u', 'lru.id_user', '=', 'u.id')
            ->join('hr_staff_info as si', 'u.id_staff', '=', 'si.FID')
            ->select('si.Nama as name', 'u.role', 'lru.created_at')
            ->where('lru.id_leave_request', $id)
            ->orderBy('lru.created_at', 'ASC')
            ->get();
        for($i = 0; $i < count($users); $i++){
            $users[$i]->name = mb_convert_case($users[$i]->name, MB_CASE_TITLE, 'UTF-8');
            $users[$i]->created_at = date('d-m-Y', strtotime($users[$i]->created_at));
        }

        $quota = [];

        $quota['dp']['outstanding'] = $leave->outstanding_dp;
        $quota['dp']['validity'] = $dpAll->count();
        $quota['dp']['approval'] = $dp->count();
        $quota['dp']['balance'] = $leave->outstanding_dp - $dp->count();

        $quota['eo']['outstanding'] = $leave->outstanding_eo;
        $quota['eo']['validity'] = $eoAll->count();
        $quota['eo']['approval'] = $eo->count();
        $quota['eo']['balance'] = $leave->outstanding_eo - $eo->count();

        $quota['al']['outstanding'] = $leave->outstanding_al;
        $quota['al']['validity'] = $alAll->count();
        $quota['al']['approval'] = $al->count();
        $quota['al']['balance'] = $leave->outstanding_al - $al->count();
            
        // return view('leave', ['data' =>$leave, 'dp' => $dp, 'eo' => $eo, 'al' => $al, 'staff' => $staff, 'id'=> $id, 'ids' => $ids, 'users' => $users, 'quota' => $quota]);
        return Pdf::view('leave', ['data' =>$leave, 'dp' => $dp, 'eo' => $eo, 'al' => $al, 'staff' => $staff, 'id'=> $id, 'ids' => $ids, 'users' => $users, 'quota' => $quota])
            ->format('a4')
            ->margins(2, 2, 2, 2, Unit::Centimeter)
            ->name('leave-'.now()->format('Y-m-d').'.pdf');
    }
}
