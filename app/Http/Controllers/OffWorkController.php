<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class OffWorkController extends Controller
{
    public function countByHRD (Request $req) {
        $filter = $req->input('filter', '');
        $sort = $req->input('sort', 'asc');
        $search = $req->input('search', '');

        $annualLeave = DB::table('hr_staff_info AS si')
            ->select([
                'si.FID AS id',
                'si.Nama AS name',
                DB::raw('SUM(CASE WHEN lra.approval = 2 THEN 1 ELSE 0 END) AS approved'),
                DB::raw('SUM(CASE WHEN lra.approval = 1 THEN 1 ELSE 0 END) AS pending'),
                DB::raw('COUNT(al.id) - SUM(CASE WHEN lra.approval = 2 THEN 1 ELSE 0 END) - SUM(CASE WHEN lra.approval = 1 THEN 1 ELSE 0 END) AS remain'),
                DB::raw('COUNT(al.id) AS total')
            ])
            ->leftJoin('annual_leave AS al', 'si.FID', '=', 'al.id_staff')
            ->leftJoin(DB::raw('(
                SELECT 
                    id_al,
                    MAX(id) AS latest_leave_request_id
                FROM 
                    leave_request_al
                GROUP BY 
                    id_al
            ) AS latest_leave_request'), 'al.id', '=', 'latest_leave_request.id_al')
            ->leftJoin('leave_request_al AS lra', 'latest_leave_request.latest_leave_request_id', '=', 'lra.id')
            ->where(function ($query) {
                $query->where('al.expire', '>', DB::raw('NOW()'))
                    ->orWhereNull('al.expire');
            })
            ->where(function($query) use ($filter) {
                $query->where('si.DEPT_NAME', 'like', '%' . $filter . '%');
            })
            ->groupBy('si.FID', 'si.Nama')
            ->orderBy('si.Nama', 'ASC')
            ->get();

        $dayPayment = DB::table('hr_staff_info AS si')
            ->select([
                'si.FID AS id',
                'si.Nama AS name',
                DB::raw('SUM(CASE WHEN lrd.approval = 2 THEN 1 ELSE 0 END) AS approved'),
                DB::raw('SUM(CASE WHEN lrd.approval = 1 THEN 1 ELSE 0 END) AS pending'),
                DB::raw('COUNT(md.id) - SUM(CASE WHEN lrd.approval = 2 THEN 1 ELSE 0 END) - SUM(CASE WHEN lrd.approval = 1 THEN 1 ELSE 0 END) AS remain'),
                DB::raw('COUNT(md.id) AS total')
            ])
            ->leftJoin('manager_on_duty AS md', 'si.FID', '=', 'md.id_staff')
            ->leftJoin(DB::raw('(
                SELECT 
                    id_mod,
                    MAX(id) AS latest_leave_request_id
                FROM 
                    leave_request_dp
                GROUP BY 
                    id_mod
            ) AS latest_leave_request'), 'md.id', '=', 'latest_leave_request.id_mod')
            ->leftJoin('leave_request_dp AS lrd', 'latest_leave_request.latest_leave_request_id', '=', 'lrd.id')
            ->where(function ($query) {
                $query->where('md.expire', '>', DB::raw('NOW()'))
                    ->orWhereNull('md.expire');
            })
            ->where(function($query) use ($filter) {
                $query->where('si.DEPT_NAME', 'like', '%' . $filter . '%');
            })
            ->groupBy('si.FID', 'si.Nama')
            ->orderBy('si.Nama', 'ASC')
            ->get();

        $extraOff = DB::table('hr_staff_info AS si')
            ->select([
                'si.FID AS id',
                'si.Nama AS name',
                DB::raw('SUM(CASE WHEN lre.approval = 2 THEN 1 ELSE 0 END) AS approved'),
                DB::raw('SUM(CASE WHEN lre.approval = 1 THEN 1 ELSE 0 END) AS pending'),
                DB::raw('COUNT(eo.id) - SUM(CASE WHEN lre.approval = 2 THEN 1 ELSE 0 END) - SUM(CASE WHEN lre.approval = 1 THEN 1 ELSE 0 END) AS remain'),
                DB::raw('COUNT(eo.id) AS total')
            ])
            ->leftJoin('extra_off as eo', 'si.FID', '=', 'eo.id_staff')
            ->leftJoin(DB::raw('(
                SELECT 
                    id_eo,
                    MAX(id) AS latest_leave_request_id
                FROM 
                    leave_request_eo
                GROUP BY 
                    id_eo
            ) AS latest_leave_request'), 'eo.id', '=', 'latest_leave_request.id_eo')
            ->leftJoin('leave_request_eo AS lre', 'latest_leave_request.latest_leave_request_id', '=', 'lre.id')
            ->where(function ($query) {
                $query->where('eo.expire', '>', DB::raw('NOW()'))
                    ->orWhereNull('eo.expire');
            })
            ->where(function($query) use ($filter) {
                $query->where('si.DEPT_NAME', 'like', '%' . $filter . '%');
            })
            ->groupBy('si.FID', 'si.Nama')
            ->orderBy('si.Nama', 'ASC')
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

    public function countByDepartment () {

        $annualLeave = DB::table('hr_staff_info AS si')
            ->select([
                'si.FID AS id',
                'si.Nama AS name',
                DB::raw('SUM(CASE WHEN lra.approval = 2 THEN 1 ELSE 0 END) AS approved'),
                DB::raw('SUM(CASE WHEN lra.approval = 1 THEN 1 ELSE 0 END) AS pending'),
                DB::raw('COUNT(al.id) - SUM(CASE WHEN lra.approval = 2 THEN 1 ELSE 0 END) - SUM(CASE WHEN lra.approval = 1 THEN 1 ELSE 0 END) AS remain'),
                DB::raw('COUNT(al.id) AS total')
            ])
            ->leftJoin('annual_leave AS al', 'si.FID', '=', 'al.id_staff')
            ->leftJoin(DB::raw('(
                SELECT 
                    id_al,
                    MAX(id) AS latest_leave_request_id
                FROM 
                    leave_request_al
                GROUP BY 
                    id_al
            ) AS latest_leave_request'), 'al.id', '=', 'latest_leave_request.id_al')
            ->leftJoin('leave_request_al AS lra', 'latest_leave_request.latest_leave_request_id', '=', 'lra.id')
            ->where(function ($query) {
                $query->where('al.expire', '>', DB::raw('NOW()'))
                    ->orWhereNull('al.expire');
            })
            ->where('si.DEPT_NAME', 'like', Auth::user()->id_unit)
            ->groupBy('si.FID', 'si.Nama')
            ->orderBy('si.Nama', 'ASC')
            ->get();

        $dayPayment = DB::table('hr_staff_info AS si')
            ->select([
                'si.FID AS id',
                'si.Nama AS name',
                DB::raw('SUM(CASE WHEN lrd.approval = 2 THEN 1 ELSE 0 END) AS approved'),
                DB::raw('SUM(CASE WHEN lrd.approval = 1 THEN 1 ELSE 0 END) AS pending'),
                DB::raw('COUNT(md.id) - SUM(CASE WHEN lrd.approval = 2 THEN 1 ELSE 0 END) - SUM(CASE WHEN lrd.approval = 1 THEN 1 ELSE 0 END) AS remain'),
                DB::raw('COUNT(md.id) AS total')
            ])
            ->leftJoin('manager_on_duty AS md', 'si.FID', '=', 'md.id_staff')
            ->leftJoin(DB::raw('(
                SELECT 
                    id_mod,
                    MAX(id) AS latest_leave_request_id
                FROM 
                    leave_request_dp
                GROUP BY 
                    id_mod
            ) AS latest_leave_request'), 'md.id', '=', 'latest_leave_request.id_mod')
            ->leftJoin('leave_request_dp AS lrd', 'latest_leave_request.latest_leave_request_id', '=', 'lrd.id')
            ->where(function ($query) {
                $query->where('md.expire', '>', DB::raw('NOW()'))
                    ->orWhereNull('md.expire');
            })
            ->where('si.DEPT_NAME', 'like', Auth::user()->id_unit)
            ->groupBy('si.FID', 'si.Nama')
            ->orderBy('si.Nama', 'ASC')
            ->get();

        $extraOff = DB::table('hr_staff_info AS si')
            ->select([
                'si.FID AS id',
                'si.Nama AS name',
                DB::raw('SUM(CASE WHEN lre.approval = 2 THEN 1 ELSE 0 END) AS approved'),
                DB::raw('SUM(CASE WHEN lre.approval = 1 THEN 1 ELSE 0 END) AS pending'),
                DB::raw('COUNT(eo.id) - SUM(CASE WHEN lre.approval = 2 THEN 1 ELSE 0 END) - SUM(CASE WHEN lre.approval = 1 THEN 1 ELSE 0 END) AS remain'),
                DB::raw('COUNT(eo.id) AS total')
            ])
            ->leftJoin('extra_off as eo', 'si.FID', '=', 'eo.id_staff')
            ->leftJoin(DB::raw('(
                SELECT 
                    id_eo,
                    MAX(id) AS latest_leave_request_id
                FROM 
                    leave_request_eo
                GROUP BY 
                    id_eo
            ) AS latest_leave_request'), 'eo.id', '=', 'latest_leave_request.id_eo')
            ->leftJoin('leave_request_eo AS lre', 'latest_leave_request.latest_leave_request_id', '=', 'lre.id')
            ->where(function ($query) {
                $query->where('eo.expire', '>', DB::raw('NOW()'))
                    ->orWhereNull('eo.expire');
            })
            ->where('si.DEPT_NAME', 'like', Auth::user()->id_unit)
            ->groupBy('si.FID', 'si.Nama')
            ->orderBy('si.Nama', 'ASC')
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

        $data = 
        // DB::table('manager_on_duty AS md')
        // ->leftJoin('leave_request_dp AS lrd', 'md.id', '=', 'lrd.id_mod')
        // ->select('md.*', 'lrd.date AS replace_date', 'lrd.approval')
        // ->where('md.id_staff', '=', $id)
        // ->where(function ($query) {
        //     $query->where('lrd.approval', '=', 2)
        //         ->orwhere('lrd.approval', '=', 1)
        //         ->orWhereNull('lrd.approval');
        // })
        // ->orderByDesc('md.date')
        DB::table('manager_on_duty AS md')
            ->select([
                'md.*', 'lrd.date AS replace_date', 'lrd.approval'
            ])
            ->leftJoin(DB::raw('(
                SELECT 
                    id_mod,
                    MAX(id) AS latest_leave_request_id
                FROM 
                    leave_request_dp
                GROUP BY 
                    id_mod
            ) AS latest_leave_request'), 'md.id', '=', 'latest_leave_request.id_mod')
            ->leftJoin('leave_request_dp AS lrd', 'latest_leave_request.latest_leave_request_id', '=', 'lrd.id')
            ->where('md.id_staff', $id)
            ->orderBy('md.date', 'DESC')
            ->paginate(10);
        
        return response()->json([
            'status' => true,
            'message' => 'Data Day Payment',
            'data' => $data,
        ], 200);
    }

    public function indexEo (Request $req) {
        $id = $req->input('id');

        $data = DB::table('extra_off as eo')
            ->select([
                'eo.*', 'lre.date AS replace_date', 'lre.approval'
            ])
            ->leftJoin(DB::raw('(
                SELECT 
                    id_eo,
                    MAX(id) AS latest_leave_request_id
                FROM 
                    leave_request_eo
                GROUP BY 
                    id_eo
            ) AS latest_leave_request'), 'eo.id', '=', 'latest_leave_request.id_eo')
            ->leftJoin('leave_request_eo AS lre', 'latest_leave_request.latest_leave_request_id', '=', 'lre.id')
            ->where('eo.id_staff', $id)
            ->orderBy('eo.date', 'DESC')
            ->paginate(10);
        
        return response()->json([
            'status' => true,
            'message' => 'Extra Off Data',
            'data' => $data,
        ], 200);
    }

    public function indexAl (Request $req) {
        $id = $req->input('id');

        $data = DB::table('annual_leave as al')
        ->select([
            'al.*', 'lra.date AS replace_date', 'lra.approval'
        ])
        ->leftJoin(DB::raw('(
            SELECT 
                id_al,
                MAX(id) AS latest_leave_request_id
            FROM 
                leave_request_al
            GROUP BY 
                id_al
        ) AS latest_leave_request'), 'al.id', '=', 'latest_leave_request.id_al')
        ->leftJoin('leave_request_al AS lra', 'latest_leave_request.latest_leave_request_id', '=', 'lra.id')
        ->where('al.id_staff', $id)
        ->orderBy('al.date', 'DESC')
        ->paginate(10);
        
        return response()->json([
            'status' => true,
            'message' => 'Annual Leave Data',
            'data' => $data,
        ], 200);
    }

    
}
