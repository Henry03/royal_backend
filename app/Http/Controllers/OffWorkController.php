<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class OffWorkController extends Controller
{
    public function countByDepartment () {
        $user = DB::table('users')
            ->join('hr_staff_info', 'users.id_staff', '=', 'hr_staff_info.FID')
            ->where('id_staff', '=', Auth::user()->id_staff)
            ->first();

        $annualLeave = DB::table('hr_staff_info AS si')
            ->leftJoin('annual_leave AS al', 'si.FID', '=', 'al.id_staff')
            ->leftJoin('leave_request_al AS lra', 'al.id', '=', 'lra.id_al')
            ->select(
                'si.FID AS id',
                'si.Nama AS name',
                DB::raw('COUNT(CASE WHEN lra.approval = 2 THEN al.id END) AS approved'),
                DB::raw('COUNT(al.id) - COUNT(CASE WHEN lra.approval = 2 THEN al.id END) AS remain'),
                DB::raw('COUNT(al.id) AS total')
            )
            ->where('si.DEPT_NAME', 'like', 'a000000006')
            ->where(function ($query) {
                $query->where('al.expire', '>', now())
                    ->orWhereNull('al.expire');
            })
            ->groupBy('si.FID', 'si.Nama')
            ->orderBy('si.Nama', 'asc')
            ->get();

        $dayPayment = DB::table('hr_staff_info AS si')
            ->leftJoin('manager_on_duty AS md', 'si.FID', '=', 'md.id_staff')
            ->leftJoin('leave_request_dp AS lrd', 'md.id', '=', 'lrd.id_mod')
            ->select(
                'si.FID AS id',
                'si.Nama AS name',
                DB::raw('COUNT(CASE WHEN lrd.approval = 2 THEN md.id END) AS approved'),
                DB::raw('COUNT(md.id) - COUNT(CASE WHEN lrd.approval = 2 THEN md.id END) AS remain'),
                DB::raw('COUNT(md.id) AS total')
            )
            ->where('si.DEPT_NAME', 'like', $user->DEPT_NAME)
            ->where(function ($query) {
                $query->where('md.expire', '>', now())
                    ->orWhereNull('md.expire');
            })
            ->groupBy('si.FID', 'si.Nama')
            ->orderBy('si.Nama', 'asc')
            ->get();

        $extraOff = DB::table('hr_staff_info AS si')
            ->leftJoin('extra_off AS eo', 'si.FID', '=', 'eo.id_staff')
            ->leftJoin('leave_request_eo AS lre', 'eo.id', '=', 'lre.id_eo')
            ->select(
                'si.FID AS id',
                'si.Nama AS name',
                DB::raw('COUNT(CASE WHEN lre.approval = 2 THEN eo.id END) AS approved'),
                DB::raw('COUNT(eo.id) - COUNT(CASE WHEN lre.approval = 2 THEN eo.id END) AS remain'),
                DB::raw('COUNT(eo.id) AS total')
            )
            ->where('si.DEPT_NAME', 'like', $user->DEPT_NAME)
            ->where(function ($query) {
                $query->where('eo.expire', '>', now())
                    ->orWhereNull('eo.expire');
            })
            ->groupBy('si.FID', 'si.Nama')
            ->orderBy('si.Nama', 'asc')
            ->get();

        $combinedData = [];

        foreach ($annualLeave as $leave) {
            $fid = $leave->id; 
            $combinedData[$fid]['id'] = $leave->id;
            $combinedData[$fid]['name'] = $leave->name;
            $combinedData[$fid]['al'] = [
                'used' => isset($leave->used) ? $leave->used : 0,
                'remain' => isset($leave->remain) ? $leave->remain : 0,
                'total' => isset($leave->total) ? $leave->total : 0,
            ];
        }

        foreach ($dayPayment as $payment) {
            $fid = $payment->id; 
            $combinedData[$fid]['id'] = $payment->id;
            $combinedData[$fid]['name'] = $payment->name;
            $combinedData[$fid]['dp'] = [
                'used' => isset($payment->used) ? $payment->used : 0,
                'remain' => isset($payment->remain) ? $payment->remain : 0,
                'total' => isset($payment->total) ? $payment->total : 0,
            ];
        }

        foreach ($extraOff as $extra) {
            $fid = $extra->id; 
            $combinedData[$fid]['id'] = $extra->id;
            $combinedData[$fid]['name'] = $extra->name;
            $combinedData[$fid]['eo'] = [
                'used' => isset($extra->used) ? $extra->used : 0,
                'remain' => isset($extra->remain) ? $extra->remain : 0,
                'total' => isset($extra->total) ? $extra->total : 0,
            ];
        }

        $combinedData = array_values($combinedData);


        return response()->json([
            'status' => true,
            'message' => 'Off Work Quota Data',
            'data' => $combinedData
        ], 200);
    }

    public function indexDp (Request $req) {
        $id = $req->input('id');

        $data = DB::table('manager_on_duty AS md')
        ->leftJoin('leave_request_dp AS lrd', 'md.id', '=', 'lrd.id_mod')
        ->select('md.*', 'lrd.date AS replace_date')
        ->where('md.id_staff', '=', $id)
        ->orderByDesc('md.date')
        ->paginate(10);
        
        return response()->json([
            'status' => true,
            'message' => 'Data Day Payment',
            'data' => $data,
        ], 200);
    }

    public function indexEo (Request $req) {
        $id = $req->input('id');

        $data = DB::table('extra_off AS eo')
        ->where('eo.id_staff', '=', $id)
        ->orderByDesc('eo.date')
        ->paginate(10);
        
        return response()->json([
            'status' => true,
            'message' => 'Extra Off Data',
            'data' => $data,
        ], 200);
    }

    public function indexAl (Request $req) {
        $id = $req->input('id');

        $data = DB::table('annual_leave AS al')
        ->where('al.id_staff', '=', $id)
        ->orderByDesc('al.date')
        ->paginate(10);
        
        return response()->json([
            'status' => true,
            'message' => 'Annual Leave Data',
            'data' => $data,
        ], 200);
    }

    
}
