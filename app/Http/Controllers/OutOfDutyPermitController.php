<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Spatie\LaravelPdf\Facades\Pdf;

class OutOfDutyPermitController extends Controller
{
    public function index (Request $request) {
        $filter = $request->input('filter', 'created_at');
        $sort = $request->input('sort', 'desc');
        $search = $request->input('search');

        $data = DB::table('out_of_duty AS od')
        ->select('od.*', 'si.Nama AS Nama', 'u.NamaUnit AS Unit')
        ->join('hr_staff_info AS si', 'od.id_staff', '=', 'si.FID')
        ->join('hr_unit AS u', 'si.DEPT_NAME', '=', 'u.IdUnit')
        ->where(function ($query) use ($search) {
            $query
                ->where(function ($query) use ($search) {
                    $query->where('track', 2)
                        ->where('status', 1);
                })
                ->orWhere(function ($query) use ($search) {
                    $query->where('track', 3)
                        ->where('status', 1);
                })
                ->orWhere(function ($query) use ($search) {
                    $query->where('track', 4)
                        ->where('status', 1);
                })
                ->orWhere(function ($query) use ($search) {
                    $query->where('track', 5)
                        ->where('status', 1);
                })
                ->orWhere('track', 6);
        })
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

    public function departmentIndex(Request $request) {
        $department = DB::table('hr_staff_info AS si')
        ->where('si.FID', '=', Auth::user()->id_staff)
        ->first();

        $data = DB::table('out_of_duty AS od')
        ->select('od.*', 'si.Nama AS Nama')
        ->join('hr_staff_info AS si', 'od.id_staff', '=', 'si.FID')
        ->where('si.DEPT_NAME', '=', $department->DEPT_NAME)
        ->where('track', 6)
        ->where('status', 1)
        ->get();

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

    public function employeeIndex (Request $request) {
        $filter = $request->input('filter', 'created_at');
        $sort = $request->input('sort', 'desc');
        $search = $request->input('search');

        $data = DB::table('out_of_duty')
        ->where('id_staff', '=', session('id_staff'))
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

    public function userIndex(Request $request) {
        $filter = $request->input('filter', 'created_at');
        $sort = $request->input('sort', 'desc');
        $search = $request->input('search');
        
        $user = DB::table('hr_staff_info AS si')
        ->where('si.FID', '=', Auth::user()->id_staff)
        ->first();
        $data = DB::table('out_of_duty AS od')
        ->join('hr_staff_info AS si', 'od.id_staff', '=', 'si.FID')
        ->where('si.DEPT_NAME', '=', $user->DEPT_NAME)
        // ->where(function ($query){
        //     $query->where('track', 2)
        //         ->orWhere('track', 3)
        //         ->orWhere('track', 4);
        // })
        ->where('status', 1)
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

    public function store (Request $request) {
        $input = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'destination' => 'required',
            'purpose' => 'required',
        ]);

        $id = session('id_staff');
        $data = DB::table('hr_staff_info AS si')
            ->join('hr_unit AS u', 'si.DEPT_NAME', '=', 'u.IdUnit')
            ->where('FID', $id)
            ->first();

        $input['id_staff'] = $id;
        
        $input['status'] = 1;   
        $input['track'] = 1;
        $input['created_at'] = now();
        $input['updated_at'] = now();

        $outOfDuty = DB::table('out_of_duty')->insert($input);

        return response()->json([
            'status' => true,
            'message' => 'Out of Duty request has been sent',
            'data' => $input
        ], 200);
    }

    public function show ($id) {
        $data = DB::table('out_of_duty')
        ->where('id', $id)
        ->first();

        return response()->json([
            'status' => true,
            'message' => 'Data Out of Duty',
            'data' => $data
        ], 200);
    }

    public function employeeCancel ($id) {
        $data = DB::table('out_of_duty')
        ->where('id_staff', session('id_staff'))
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

    public function approve ($id) {
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
        $user = DB::table('out_of_duty_update')
            ->insert([
                'id_out_of_duty' => $id,
                'id_user' => Auth::user()->id,
                'track' => $track,
                'created_at' => now(),
                'updated_at' => now()
            ]);

        $data = DB::table('out_of_duty')
        ->where('id', $id)
        ->update(['track' => $track]);

        if($data){
            return response()->json([
                'status' => true,
                'message' => 'Out of Duty request has been approved',
                'data' => null
            ], 200);
        }

        return response()->json([
            'status' => false,
            'message' => 'Out of Duty request failed to approve',
            'data' => null
        ], 200);
    }

    public function reject ($id) {
        if(Auth::user()->role == 2){
            $track = 2;
        }else if(Auth::user()->role == 3){
            $track = 3;
        }else if(Auth::user()->role == 4){
            $track = 4;
        }
        $data = DB::table('out_of_duty')
        ->where('id', $id)
        ->update(['status' => 0, 'track' => $track]);

        $data = DB::table('out_of_duty')
        ->where('id', $id)
        ->update(['track' => $track]);

        if($data){
            return response()->json([
                'status' => true,
                'message' => 'Out of Duty request has been rejected',
                'data' => null
            ], 200);
        }

        return response()->json([
            'status' => false,
            'message' => 'Out of Duty request failed to reject',
            'data' => null
        ], 200);
    }

    public function download (Request $request) {
        $id = $request->input('id');
        $ids = $request->input('ids');

        $data = DB::table('out_of_duty AS od')
        ->join('hr_staff_info AS si', 'od.id_staff', '=', 'si.FID')
        ->select('si.Nama AS name', 'od.*')
        ->where('id', $id)
        ->where('status', 1)
        ->where('track', 6)
        ->where('od.id_staff', $ids)
        ->first();

        $user = DB::table('out_of_duty_update AS ou')
        ->join('users AS u', 'ou.id_user', '=', 'u.id')
        ->join('hr_staff_info AS si', 'u.id_staff', '=', 'si.FID')
        ->select('si.Nama AS name', 'u.role', 'ou.*')
        ->where('ou.id_out_of_duty', $id)
        ->get();

        $data->date = date('l, d F Y', strtotime($data->start_date));
        $data->start_time = date('H:m', strtotime($data->start_date));
        $data->end_time = date('H:m', strtotime($data->end_date));
        for($i = 0; $i < count($user); $i++){
            $user[$i]->name = mb_convert_case($user[$i]->name, MB_CASE_TITLE, 'UTF-8');
        }
        return Pdf::view('outofduty', ['data' => $data, 'users' => $user])
            ->format('a4')
            ->name('out_of_duty-'.now()->format('Y-m-d').'.pdf');
    }
}
