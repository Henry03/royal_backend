<?php

namespace App\Http\Controllers;

use HeadlessChromium\BrowserFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Spatie\Browsershot\Browsershot;
use Spatie\LaravelPdf\Facades\Pdf;
use Spatie\LaravelPdf\Enums\Unit;
use Telegram\Bot\Laravel\Facades\Telegram;

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
            ->leftJoin('staff as si', 'si.id', '=', 'lr.id_staff')
            ->leftJoin(DB::raw('(SELECT us.id_staff, us.role FROM users AS us WHERE us.role = 5) as us'), function ($join) {
                $join->on('si.id', '=', 'us.id_staff');
            })
            ->select(
                'md.id_staff',
                'lr.id',
                'lr.request_date',
                'lr.note',
                'lr.status',
                'lr.track',
                'si.name'
            )
            ->where(function ($query){
                $query
                    ->where(function ($query){
                        $query->where('track', 2)
                            ->where('lr.status', 1);
                    })
                    ->orWhere(function ($query){
                        $query->where('track', 3)
                            ->where('lr.status', 1);
                    })
                    ->orWhere(function ($query){
                        $query->where('track', 4)
                            ->where('lr.status', 1);
                    })
                    ->orWhere(function ($query) {
                        $query->where('track', 5)
                            ->where('lr.status', 1);
                    })
                    ->orWhere('track', 6)
                    ->orWhere(function ($query) {
                        $query->where('track', 1)
                            ->whereNotNull('us.role');
                    });
            })
            ->groupBy('lr.id', 'lr.request_date', 'lr.note', 'lr.status', 'lr.track', 'md.id_staff', 'eo.id_staff', 'al.id_staff', 'si.name')
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

        $data = DB::table('leave_request AS lr')
            ->select(
                'lr.id',
                'lr.request_date',
                'lr.note',
                'lr.status',
                'lr.track',
                'si.name'
            )
            ->leftJoin('leave_request_dp AS lrd', 'lrd.id_leave_request', '=', 'lr.id')
            ->leftJoin('leave_request_eo AS lre', 'lre.id_leave_request', '=', 'lr.id')
            ->leftJoin('leave_request_al AS lra', 'lra.id_leave_request', '=', 'lr.id')
            ->leftJoin('manager_on_duty AS md', 'md.id', '=', 'lrd.id_mod')
            ->leftJoin('extra_off AS eo', 'eo.id', '=', 'lre.id_eo')
            ->leftJoin('annual_leave AS al', 'al.id', '=', 'lra.id_al')
            ->leftJoin('staff AS si', function($join) {
                $join->on('si.id', '=', 'md.id_staff')
                    ->orOn('si.id', '=', 'eo.id_staff')
                    ->orOn('si.id', '=', 'al.id_staff');
            })
            ->leftJoin(DB::raw('(SELECT id_staff, id, role, deleted_at
                FROM users
                WHERE deleted_at IS NULL) AS us'), 'us.id_staff', '=', 'si.id')
            ->where('si.id_unit', Auth::user()->id_unit)
            ->where(function ($query) {
                $query->where(function ($query) {
                    $query->where('lr.track', 1)
                        ->where('lr.status', 1);
                })
                ->orWhereIn('lr.track', [2, 3, 4, 5, 6]);
            })
            ->where(function ($query) {
                $query->where('us.role', '<', Auth::user()->role)
                    ->orWhereNull('us.role');
            })
            ->whereNull('us.deleted_at')
            ->groupBy('lr.id', 'lr.request_date', 'lr.note', 'lr.status', 'lr.track', 'md.id_staff', 'eo.id_staff', 'al.id_staff', 'si.name')
            ->orderBy($filter, $sort)
            ->paginate(10);

        return response()->json([
            'status' => true,
            'message' => 'Data Leave Request',
            'data' => $data
        ], 200);
    }

    public function indexbyGm (Request $request) {
        $filter = $request->input('filter', 'request_date');
        $sort = $request->input('sort', 'desc');

        $data = DB::table('leave_request AS lr')
            ->select(
                'lr.id',
                'lr.request_date',
                'lr.note',
                'lr.status',
                'lr.track',
                'si.name'
            )
            ->leftJoin('leave_request_dp AS lrd', 'lrd.id_leave_request', '=', 'lr.id')
            ->leftJoin('leave_request_eo AS lre', 'lre.id_leave_request', '=', 'lr.id')
            ->leftJoin('leave_request_al AS lra', 'lra.id_leave_request', '=', 'lr.id')
            ->leftJoin('manager_on_duty AS md', 'md.id', '=', 'lrd.id_mod')
            ->leftJoin('extra_off AS eo', 'eo.id', '=', 'lre.id_eo')
            ->leftJoin('annual_leave AS al', 'al.id', '=', 'lra.id_al')
            ->leftJoin('staff AS si', function($join) {
                $join->on('si.id', '=', 'md.id_staff')
                    ->orOn('si.id', '=', 'eo.id_staff')
                    ->orOn('si.id', '=', 'al.id_staff');
            })
            ->leftJoin('users AS us', 'us.id_staff', '=', 'si.id')
            ->where(function ($query) {
                $query->where(function ($query) {
                    $query->where('lr.track', 1)
                        ->where('lr.status', 1);
                })
                ->orWhereIn('lr.track', [2, 3, 4, 5, 6]);
            })
            ->where(function ($query) {
                $query->where('us.role', '=', 4);
            })
            ->whereNull('us.deleted_at')
            ->groupBy('lr.id', 'lr.request_date', 'lr.note', 'lr.status', 'lr.track', 'md.id_staff', 'eo.id_staff', 'al.id_staff', 'si.name')
            ->orderBy($filter, $sort)
            ->paginate(10);

        return response()->json([
            'status' => true,
            'message' => 'Data Leave Request',
            'data' => $data
        ], 200);
    }

    public function indexAllCalendar (Request $request) {

        $dp = DB::table('leave_request_dp as lrd')
            ->join('manager_on_duty as md', 'lrd.id_mod', '=', 'md.id')
            ->join('staff as md_hr', 'md.id_staff', '=', 'md_hr.id')
            ->select('lrd.date as replace_date', 'md.date', 'md_hr.name')
            ->where('lrd.approval', 2)
            ->get();
        $eo = DB::table('leave_request_eo as lre')
            ->join('extra_off as eo', 'lre.id_eo', '=', 'eo.id')
            ->join('staff as eo_hr', 'eo.id_staff', '=', 'eo_hr.id')
            ->select('lre.date as replace_date', 'eo.date', 'eo_hr.name')
            ->where('lre.approval', 2)
            ->get();
        $al = DB::table('leave_request_al as lra')
            ->join('annual_leave as al', 'lra.id_al', '=', 'al.id')
            ->join('staff as al_hr', 'al.id_staff', '=', 'al_hr.id')
            ->select('lra.date as replace_date', 'al.date', 'al_hr.name')
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

    public function indexbyGmApproved (Request $request) {

        $dp = DB::table('leave_request_dp as lrd')
            ->join('manager_on_duty as md', 'lrd.id_mod', '=', 'md.id')
            ->join('staff as si', 'md.id_staff', '=', 'si.id')
            ->join(DB::raw('(SELECT id_staff, id, role, deleted_at FROM users WHERE role = 4 AND deleted_at IS NULL) as u'), 'u.id_staff', '=', 'si.id')
            ->select('lrd.date as replace_date', 'md.date', 'si.name')
            ->where('lrd.approval', 2)
            ->get();
        $eo = DB::table('leave_request_eo as lre')
            ->join('extra_off as eo', 'lre.id_eo', '=', 'eo.id')
            ->join('staff as si', 'eo.id_staff', '=', 'si.id')
            ->join(DB::raw('(SELECT id_staff, id, role, deleted_at FROM users WHERE role = 4 AND deleted_at IS NULL) as u'), 'u.id_staff', '=', 'si.id')
            ->select('lre.date as replace_date', 'eo.date', 'si.name')
            ->where('lre.approval', 2)
            ->get();
        $al = DB::table('leave_request_al as lra')
            ->join('annual_leave as al', 'lra.id_al', '=', 'al.id')
            ->join('staff as si', 'al.id_staff', '=', 'si.id')
            ->join(DB::raw('(SELECT id_staff, id, role, deleted_at FROM users WHERE role = 4 AND deleted_at IS NULL) as u'), 'u.id_staff', '=', 'si.id')
            ->select('lra.date as replace_date', 'al.date', 'si.name')
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

    public function indexbyDepartmentApproved (Request $request) {

        $id = Auth::user()->id_unit;

        $dp = DB::table('leave_request_dp as lrd')
            ->join('manager_on_duty as md', 'lrd.id_mod', '=', 'md.id')
            ->join('staff as md_hr', 'md.id_staff', '=', 'md_hr.id')
            ->select('lrd.date as replace_date', 'md.date', 'md_hr.name')
            ->where('lrd.approval', 2)
            ->where('md_hr.id_unit', '=', $id)
            ->get();
        $eo = DB::table('leave_request_eo as lre')
            ->join('extra_off as eo', 'lre.id_eo', '=', 'eo.id')
            ->join('staff as eo_hr', 'eo.id_staff', '=', 'eo_hr.id')
            ->select('lre.date as replace_date', 'eo.date', 'eo_hr.name')
            ->where('lre.approval', 2)
            ->where('eo_hr.id_unit', '=', $id)
            ->get();
        $al = DB::table('leave_request_al as lra')
            ->join('annual_leave as al', 'lra.id_al', '=', 'al.id')
            ->join('staff as al_hr', 'al.id_staff', '=', 'al_hr.id')
            ->select('lra.date as replace_date', 'al.date', 'al_hr.name')
            ->where('lra.approval', 2)
            ->where('al_hr.id_unit', '=', $id)
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Data Leave Request',
            'dp' => $dp,
            'eo' => $eo,
            'al' => $al
        ], 200);
    }

    public function indexbyEmployeeApproved (Request $request) {

        $id = session('id_staff');

        $dp = DB::table('leave_request_dp as lrd')
            ->join('manager_on_duty as md', 'lrd.id_mod', '=', 'md.id')
            ->join('staff as md_hr', 'md.id_staff', '=', 'md_hr.id')
            ->select('lrd.date as replace_date', 'md.date', 'md_hr.name')
            ->where('lrd.approval', 2)
            ->where('md_hr.id', '=', $id)
            ->get();
        $eo = DB::table('leave_request_eo as lre')
            ->join('extra_off as eo', 'lre.id_eo', '=', 'eo.id')
            ->join('staff as eo_hr', 'eo.id_staff', '=', 'eo_hr.id')
            ->select('lre.date as replace_date', 'eo.date', 'eo_hr.name')
            ->where('lre.approval', 2)
            ->where('eo_hr.id', '=', $id)
            ->get();
        $al = DB::table('leave_request_al as lra')
            ->join('annual_leave as al', 'lra.id_al', '=', 'al.id')
            ->join('staff as al_hr', 'al.id_staff', '=', 'al_hr.id')
            ->select('lra.date as replace_date', 'al.date', 'al_hr.name')
            ->where('lra.approval', 2)
            ->where('al_hr.id', '=', $id)
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
            ->leftJoin('leave_request_dp AS lrd', 'lrd.id_leave_request', '=', 'lr.id')
            ->leftJoin('leave_request_eo AS lre', 'lre.id_leave_request', '=', 'lr.id')
            ->leftJoin('leave_request_al AS lra', 'lra.id_leave_request', '=', 'lr.id')
            ->leftJoin('manager_on_duty AS md', 'md.id', '=', 'lrd.id_mod')
            ->leftJoin('extra_off AS eo', 'eo.id', '=', 'lre.id_eo')
            ->leftJoin('annual_leave AS al', 'al.id', '=', 'lra.id_al')
            ->join('staff AS si', function ($join) {
                $join->on('si.id', '=', 'md.id_staff')
                    ->orOn('si.id', '=', 'eo.id_staff')
                    ->orOn('si.id', '=', 'al.id_staff');
            })
            ->select(DB::raw('CASE
                WHEN md.id_staff IS NOT NULL THEN md.id_staff
                WHEN eo.id_staff IS NOT NULL THEN eo.id_staff
                ELSE al.id_staff
            END AS id_staff'), 'lr.id', 'lr.request_date', 'lr.note', 'lr.status', 'lr.track')
            ->where('si.id', '=', session('id_staff'))
            ->groupBy('id_staff', 'lr.id', 'lr.request_date', 'lr.note', 'lr.status', 'lr.track')
            ->orderBy($filter, $sort)
            ->paginate(10);

        return response()->json([
            'status' => true,
            'message' => 'Data Leave Request',
            'data' => $data
        ], 200);
    }

    public function indexEmployeeQuota () {
        $dp = DB::table('manager_on_duty AS md')
            ->select([
                'md.*',
                'lrd.approval'
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
            ->where(function ($query) {
                $query->where('md.expire', '>', DB::raw('NOW()'))
                    ->orWhereNull('md.expire');
            })
            ->where(function ($query) {
                $query->whereNull('lrd.approval')
                    ->orWhere('lrd.approval', 0)
                    ->orWhere('lrd.approval', 3);
            })
            ->where('md.id_staff', session('id_staff'))
            ->orderBy('md.date', 'ASC')
            ->get();

        $eo = DB::table('extra_off AS eo')
        ->select([
            'eo.*',
            'lre.approval'
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
        ->where(function ($query) {
            $query->where('eo.expire', '>', DB::raw('NOW()'))
                ->orWhereNull('eo.expire');
        })
        ->where(function ($query) {
            $query->whereNull('lre.approval')
                ->orWhere('lre.approval', 0)
                ->orWhere('lre.approval', 3);
        })
        ->where('eo.id_staff', session('id_staff'))
        ->orderBy('eo.date', 'ASC')
        ->get();

        $al = DB::table('annual_leave AS al')
        ->select([
            'al.*',
            'lra.approval'
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
        ->where(function ($query) {
            $query->where('al.expire', '>', DB::raw('NOW()'))
                ->orWhereNull('al.expire');
        })
        ->where(function ($query) {
            $query->whereNull('lra.approval')
                ->orWhere('lra.approval', 0)
                ->orWhere('lra.approval', 3);
        })
        ->where('al.id_staff', session('id_staff'))
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

    public function countEmployeeQuota () {
        $dp = DB::table(function($subquery) {
                $subquery->select(
                        DB::raw('SUM(CASE WHEN (md.expire > NOW() OR md.expire IS NULL) AND (lrd.approval IS NULL OR lrd.approval IN (0,3)) THEN 1 ELSE 0 END) AS quota'),
                        DB::raw('SUM(CASE WHEN lrd.approval = 2 THEN 1 ELSE 0 END) AS used'),
                        DB::raw('SUM(CASE WHEN md.expire < NOW() AND (lrd.approval NOT IN (1,2) OR lrd.approval IS null) THEN 1 ELSE 0 END) AS expired'),
                        DB::raw('SUM(CASE WHEN lrd.approval = 1 THEN 1 ELSE 0 END) AS pending'),
                        DB::raw('COUNT(DISTINCT md.id) AS total')
                    )
                    ->from('manager_on_duty AS md')
                    ->leftJoin(DB::raw('(SELECT 
                                            id_mod, 
                                            approval
                                        FROM 
                                            leave_request_dp
                                        GROUP BY 
                                            id_mod, approval) AS lrd'), 'md.id', '=', 'lrd.id_mod')
                    ->where('md.id_staff', session('id_staff'))
                    ->where(function ($query) {
                        $query->whereRaw('md.expire > DATE_FORMAT(NOW(), "%Y-%m-01")')
                            ->orWhereNull('md.expire');
                    })
                    ->groupBy('md.id');
            }, 'quota')
            ->select(
                DB::raw('SUM(CASE WHEN quota.quota > 0 THEN 1 ELSE 0 END) as quota'),
                DB::raw('SUM(CASE WHEN quota.used > 0 THEN 1 ELSE 0 END) as used'),
                DB::raw('SUM(CASE WHEN quota.pending > 0 THEN 1 ELSE 0 END) as pending'),
                DB::raw('SUM(CASE WHEN quota.expired > 0 THEN 1 ELSE 0 END) as expired'),
                DB::raw('SUM(CASE WHEN quota.total > 0 THEN 1 ELSE 0 END) as total')
            )
            ->first();

        $dpBalance = [];
        if ($dp) {
            $dpBalance[] = [
                'id' => 'quota',
                'label' => 'Quota',
                'value' => $dp->quota,
                'color' => 'hsl(130, 70%, 50%)'

            ];
            $dpBalance[] = [
                'id' => 'used',
                'label' => 'Used',
                'value' => $dp->used,
                'color' => 'hsl(221, 70%, 50%)'
            ];
            $dpBalance[] = [
                'id' => 'pending',
                'label' => 'Pending',
                'value' => $dp->pending,
                'color' => 'hsl(54, 70%, 50%)'
            ];
            $dpBalance[] = [
                'id' => 'expired',
                'label' => 'Expired',
                'value' => $dp->expired,
                'color' => 'hsl(15, 70%, 50%)'
            ];
        }
        $eo = DB::table(function($subquery) {
                $subquery->select(
                        DB::raw('SUM(CASE WHEN (eo.expire > NOW() OR eo.expire IS NULL) AND (lre.approval IS NULL OR lre.approval IN (0,3)) THEN 1 ELSE 0 END) AS quota'),
                        DB::raw('SUM(CASE WHEN lre.approval = 2 THEN 1 ELSE 0 END) AS used'),
                        DB::raw('SUM(CASE WHEN eo.expire < NOW() AND (lre.approval NOT IN (1,2) OR lre.approval IS null) THEN 1 ELSE 0 END) AS expired'),
                        DB::raw('SUM(CASE WHEN lre.approval = 1 THEN 1 ELSE 0 END) AS pending'),
                        DB::raw('COUNT(DISTINCT eo.id) AS total')
                    )
                    ->from('extra_off AS eo')
                    ->leftJoin(DB::raw('(SELECT 
                                            id_eo, 
                                            approval
                                        FROM 
                                            leave_request_eo
                                        GROUP BY 
                                            id_eo, approval) AS lre'), 'eo.id', '=', 'lre.id_eo')
                    ->where('eo.id_staff', session('id_staff'))
                    ->where(function ($query) {
                        $query->whereRaw('eo.expire > DATE_FORMAT(NOW(), "%Y-%m-01")')
                            ->orWhereNull('eo.expire');
                    })
                    ->groupBy('eo.id');
            }, 'quota')
            ->select(
                DB::raw('SUM(CASE WHEN quota.quota > 0 THEN 1 ELSE 0 END) as quota'),
                DB::raw('SUM(CASE WHEN quota.used > 0 THEN 1 ELSE 0 END) as used'),
                DB::raw('SUM(CASE WHEN quota.pending > 0 THEN 1 ELSE 0 END) as pending'),
                DB::raw('SUM(CASE WHEN quota.expired > 0 THEN 1 ELSE 0 END) as expired'),
                DB::raw('SUM(CASE WHEN quota.total > 0 THEN 1 ELSE 0 END) as total')
            )
            ->first();

        $eoBalance = [];
        if ($eo) {
            $eoBalance[] = [
                'id' => 'quota',
                'label' => 'Quota',
                'value' => $eo->quota,
                'color' => 'hsl(130, 70%, 50%)'

            ];
            $eoBalance[] = [
                'id' => 'used',
                'label' => 'Used',
                'value' => $eo->used,
                'color' => 'hsl(221, 70%, 50%)'
            ];
            $eoBalance[] = [
                'id' => 'pending',
                'label' => 'Pending',
                'value' => $eo->pending,
                'color' => 'hsl(54, 70%, 50%)'
            ];
            $eoBalance[] = [
                'id' => 'expired',
                'label' => 'Expired',
                'value' => $eo->expired,
                'color' => 'hsl(15, 70%, 50%)'
            ];
        }
        $al = DB::table(function($subquery) {
                $subquery->select(
                        DB::raw('SUM(CASE WHEN (al.expire > NOW() OR al.expire IS NULL) AND (lra.approval IS NULL OR lra.approval IN (0,3)) THEN 1 ELSE 0 END) AS quota'),
                        DB::raw('SUM(CASE WHEN lra.approval = 2 THEN 1 ELSE 0 END) AS used'),
                        DB::raw('SUM(CASE WHEN al.expire < NOW() AND (lra.approval NOT IN (1,2) OR lra.approval IS null) THEN 1 ELSE 0 END) AS expired'),
                        DB::raw('SUM(CASE WHEN lra.approval = 1 THEN 1 ELSE 0 END) AS pending'),
                        DB::raw('COUNT(DISTINCT al.id) AS total')
                    )
                    ->from('annual_leave as al')
                    ->leftJoin(DB::raw('(SELECT 
                                            id_al, 
                                            approval
                                        FROM 
                                            leave_request_al
                                        GROUP BY 
                                            id_al, approval) AS lra'), 'al.id', '=', 'lra.id_al')
                    ->where('al.id_staff', session('id_staff'))
                    ->where(function ($query) {
                        $query->whereRaw('al.expire > DATE_FORMAT(NOW(), "%Y-%m-01")')
                            ->orWhereNull('al.expire');
                    })
                    ->groupBy('al.id');
            }, 'quota')
            ->select(
                DB::raw('SUM(CASE WHEN quota.quota > 0 THEN 1 ELSE 0 END) as quota'),
                DB::raw('SUM(CASE WHEN quota.used > 0 THEN 1 ELSE 0 END) as used'),
                DB::raw('SUM(CASE WHEN quota.pending > 0 THEN 1 ELSE 0 END) as pending'),
                DB::raw('SUM(CASE WHEN quota.expired > 0 THEN 1 ELSE 0 END) as expired'),
                DB::raw('SUM(CASE WHEN quota.total > 0 THEN 1 ELSE 0 END) as total')
            )
            ->first();

        $alBalance = [];
        if ($al) {
            $alBalance[] = [
                'id' => 'quota',
                'label' => 'Quota',
                'value' => $al->quota,
                'color' => 'hsl(130, 70%, 50%)'
            ];
            $alBalance[] = [
                'id' => 'used',
                'label' => 'Used',
                'value' => $al->used,
                'color' => 'hsl(221, 70%, 50%)'
            ];
            $alBalance[] = [
                'id' => 'pending',
                'label' => 'Pending',
                'value' => $al->pending,
                'color' => 'hsl(54, 70%, 50%)'
            ];
            $alBalance[] = [
                'id' => 'expired',
                'label' => 'Expired',
                'value' => $al->expired,
                'color' => 'hsl(15, 70%, 50%)'
            ];
        }

        return response()->json([
            'status' => true,
            'message' => 'Leave Balance',
            'dp' => $dpBalance,
            'eo' => $eoBalance,
            'al' => $alBalance
        ], 200);
    }

    public function indexEobyDepartment (Request $request) {
        $input = $request->validate([
            'date' => 'required|date',
            'filter' => 'string',
            'sort' => 'string'
        ]);

        $data = DB::table('extra_off as eo')
        ->select('si.name as name', 'eo.expire', 'lre.date as replace', 'lre.approval')
        ->join('staff as si', 'si.id', '=', 'eo.id_staff')
        ->leftJoin('leave_request_eo as lre', 'lre.id_eo', '=', 'eo.id')
        ->where(DB::raw("DATE_FORMAT(eo.date, '%Y-%m')"), $input['date'])
        ->where('si.id_unit', Auth::user()->id_unit)
        ->orderBy($input['filter'], $input['sort'])
        ->paginate(10);

        if($data){
            return response()->json([
                'status' => true,
                'message' => 'Data Extra Off',
                'data' => $data
            ], 200);
        }

        return response()->json([
            'status' => false,
            'message' => 'Data Extra Off not found',
            'data' => null
        ], 404);
    }

    public function indexNoEobyDepartment (Request $request) {
        $input = $request->validate([
            'date' => 'required|date'
        ]);
        
        $data = DB::table('staff as si')
            ->select('si.id', 'si.name', 'si.position')
            ->where('si.id_unit', Auth::user()->id_unit)
            ->whereNotExists(function ($query) use ($input) {
                $query->select(DB::raw(1))
                    ->from('extra_off as eo')
                    ->whereRaw('eo.id_staff = si.id')
                    ->where(DB::raw("DATE_FORMAT(eo.date, '%Y-%m')"), $input['date']);
            })
            ->where(function ($query) {
                $query->where('si.status', 'Active')
                    ->orWhereNull('si.status');
            })
            ->orderBy('si.name', 'ASC')
            ->get();

        if($data){
            return response()->json([
                'status' => true,
                'message' => 'Data Staff without Extra Off',
                'data' => $data
            ], 200);
        }

        return response()->json([
            'status' => false,
            'message' => 'Data Staff without Extra Off not found',
            'data' => null
        ], 404);
    }
    public function indexNoEo (Request $request) {     
        $unit = $request->input('unit', '');   
        $data = DB::table('staff as si')
            ->leftJoin('eo_entitlement as eoe', 'si.id', '=', 'eoe.id_staff')
            ->select('si.id', 'si.name', 'si.position')
            ->where(function ($query) {
                $query->where('si.status', 'Active')
                    ->orWhereNull('si.status');
            })
            ->where('si.id_unit', 'like', '%'.$unit.'%')
            ->whereNull('eoe.id_staff')
            ->orderBy('si.name', 'ASC')
            ->get();

        if($data){
            return response()->json([
                'status' => true,
                'message' => 'Data Staff without Extra Off',
                'data' => $data
            ], 200);
        }

        return response()->json([
            'status' => false,
            'message' => 'Data Staff without Extra Off not found',
            'data' => null
        ], 404);
    }

    public function indexStaffWithEobyDepartment (Request $request){
        $unit = $request->input('unit', '');
        $filter = $request->input('filter', 'name');
        $sort = $request->input('sort', 'asc');
        $search = $request->input('search', '');
        
        $data = DB::table('staff as si')
            ->join('hr_unit as u', 'si.id_unit', '=', 'u.IdUnit')
            ->join('eo_entitlement as eoe', 'si.id', '=', 'eoe.id_staff')
            ->where('si.id_unit', 'like', '%'.$unit.'%')
            ->where(function ($query) use ($search) {
                $query->where('si.name', 'like', '%'.$search.'%')
                    ->orWhere('si.id', 'like', '%'.$search.'%');
            })  
            ->select('si.id', 'si.name', 'u.Namaunit as department','eoe.id as id_eoe', 'eoe.id_staff') // Adjust this to select specific columns if needed
            ->orderBy($filter, $sort)
            ->paginate();

        return response()->json([
            'status' => true,
            'message' => 'Data Staff with Extra Off',
            'data' => $data
        ], 200);
    }

    public function store(Request $request) {
        $request->validate([
            'inputDp' => 'array',
            'inputDp.*.date' => 'required|date',
            'inputEo' => 'array',
            'inputEo.*.date' => 'required|date',
            'inputAl' => 'array',
            'inputAl.*.date' => 'required|date',
            'note' => 'required|string'
        ],[
            'inputDp.required' => 'Day Payment is required.',
            'inputDp.*.date.required' => 'Day Payment Date is required.',
            'inputDp.*.date.date' => 'Invalid Date format.',
            'inputEo.required' => 'Extra Off is required.',
            'inputEo.*.date.required' => 'Extra Off Date is required.',
            'inputEo.*.date.date' => 'Invalid Date format.',
            'inputAl.required' => 'Annual Leave is required.',
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
            'id_staff' => session('id_staff'),
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

        $employee = DB::table('staff')
            ->select('id_unit as id_department', 'name', 'position', 'phone_number')
            ->where('id', session('id_staff'))
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

        $dp = DB::table('manager_on_duty as md')
            ->select('md.*')
            ->distinct()
            ->leftJoin('leave_request_dp as lrd', 'md.id', '=', 'lrd.id_mod')
            ->where('md.id_staff', session('id_staff'))
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
            ->orderByRaw('ISNULL(md.expire), md.expire, md.date ASC')
            ->get();

        $eo = DB::table('extra_off as eo')
        ->select('eo.*')
        ->distinct()
        ->leftJoin('leave_request_eo as lre', 'eo.id', '=', 'lre.id_eo')
        ->where('eo.id_staff', '=', session('id_staff'))
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
        ->where('al.id_staff', '=', session('id_staff'))
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
                $dpDate[$key]['id_mod'] = $dp[$key]->id;
                $dpDate[$key]['date'] = $value['date'];
                $dpDate[$key]['approval'] = 1;
            }
            $status = DB::table('leave_request_dp')->insert($dpDate);
        }

        if($input['inputEo']){
            foreach ($input['inputEo'] as $key => $value) {
                $eoDate[$key]['id_leave_request'] = $leave->id;
                $eoDate[$key]['id_eo'] = $eo[$key]->id;
                $eoDate[$key]['date'] = $value['date'];
                $eoDate[$key]['approval'] = 1;
            }
            $status = DB::table('leave_request_eo')->insert($eoDate);
        }

        if($input['inputAl']){
            foreach ($input['inputAl'] as $key => $value) {
                $alDate[$key]['id_leave_request'] = $leave->id;
                $alDate[$key]['id_al'] = $al[$key]->id;
                $alDate[$key]['date'] = $value['date'];
                $alDate[$key]['approval'] = 1;
            }
            $status = DB::table('leave_request_al')->insert($alDate);
        }

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
        ->where('users.deleted_at', null)
        ->get();

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
        $message = "Dear bapak/ibu, saya ingin mengajukan izin cuti dengan detail sebagai berikut :";
        $message .= "%0aName        : " . $employee->name;
        $message .= "%0aPosition    : " . $employee->position;
        
        if($input['inputDp']){
            $message .= "%0a%0aDay Payment : ";
            foreach ($input['inputDp'] as $key => $value) {
                $message .= "%0a  ".($key+1).". Date     : " . date('l, d F Y', strtotime($value['date']));
            }
        }
        if($input['inputEo']){
            $message .= "%0a%0aExtra Off : ";
            foreach ($input['inputEo'] as $key => $value) {
                $message .= "%0a  ".($key+1).". Date     : " . date('l, d F Y', strtotime($value['date']));
            }
        }
        if($input['inputAl']){
            $message .= "%0a%0aAnnual Leave : ";
            foreach ($input['inputAl'] as $key => $value) {
                $message .= "%0a  ".($key+1).". Date     : " . date('l, d F Y', strtotime($value['date']));
            }
        }

        $message .= "%0a%0aNote     : " . $input['note'];
        $message .= "%0a%0aMohon dibantu untuk approval dari sistem. Terima kasih";

        return response()->json([
            'status' => 'Data Leave Permit berhasil ditambahkan',
            'message' => $message,
            'data' => $status,
            'phone_number' => $employee->phone_number
        ], 200);
    }

    public function storeEo (Request $request){
        $input = $request->validate([
            'data' => 'required|array',
            'date' => 'required|date',
        ],[
            'data.required' => 'Choose atleast one staff.',
            'data.array' => 'Data must be an array.',
            'date.required' => 'Date is required.',
            'date.date' => 'Invalid Date format.'
        
        ]);
        $expireDate = date('Y-m-t', strtotime($input['date']));
        foreach($input['data'] as $data){
            $status = DB::table('extra_off')
                ->insert([
                    'id_staff' => $data['id'],
                    'date' => $input['date'].'-01',
                    'expire' => $expireDate
                ]);
        }

        if($status){
            return response()->json([
                'status' => true,
                'message' => 'Data Extra Off berhasil ditambahkan',
                'data' => $status
            ], 200);
        }

        return response()->json([
            'status' => false,
            'message' => 'Data Extra Off gagal ditambahkan',
            'data' => null
        ], 422);
    }

    public function storeEoEntitlement (Request $request){
        $input = $request->validate([
            'data' => 'required|array'
        ],[
            'data.required' => 'Choose atleast one staff.',
            'data.array' => 'Data must be an array.'
        ]);

        foreach($input['data'] as $data){
            $status = DB::table('eo_entitlement')
                ->insert([
                    'id_staff' => $data['id']
                ]);
        }

        if($status){
            return response()->json([
                'status' => true,
                'message' => 'Data Extra Off berhasil ditambahkan',
                'data' => $status
            ], 200);
        }

        return response()->json([
            'status' => false,
            'message' => 'Data Extra Off gagal ditambahkan',
            'data' => null
        ], 422);
    }

    public function show($id) {
        $data = DB::table('leave_request')
        ->leftJoin(DB::raw('(SELECT users.id_staff, users.deleted_at, users.role FROM users WHERE users.deleted_at IS NULL) AS us'), function ($join) {
            $join->on('leave_request.id_staff', '=', 'us.id_staff');
        })
        ->join('staff as si', 'si.id', '=', 'leave_request.id_staff')
        ->select('leave_request.*', 'us.*', 'si.name')
        ->where('leave_request.id', $id)
        ->first();

        // $leave = DB::table('leave_request as lr')
        //     ->select('lr.*')
        //     ->where('lr.id', $id)
        //     ->first();

        $step = DB::table('leave_request as lr')
            ->select(DB::raw("
                CASE
                    WHEN lru.track = 1 AND lru.status = 1 THEN 'Approved by Admin'
                    WHEN lru.track = 2 AND lru.status = 1 THEN 'Approved by Chief'
                    WHEN lru.track = 3 AND lru.status = 1 THEN 'Approved Asst. HOD'
                    WHEN lru.track = 4 AND lru.status = 1 THEN 'Approved by HOD'
                    WHEN lru.track = 5 AND lru.status = 1 THEN 'Approved by GM'
                    WHEN lru.track = 6 AND lru.status = 1 THEN 'Acknowledge by HRD'
                    WHEN lru.track = 0 AND lru.status = 0 THEN 'Canceled by Employee'
                    WHEN lru.track = 1 AND lru.status = 0 THEN 'Rejected by Admin'
                    WHEN lru.track = 2 AND lru.status = 0 THEN 'Rejected by Chief'
                    WHEN lru.track = 3 AND lru.status = 0 THEN 'Rejected Asst. HOD'
                    WHEN lru.track = 4 AND lru.status = 0 THEN 'Rejected by HOD'
                    WHEN lru.track = 5 AND lru.status = 0 THEN 'Rejected by GM'
                    WHEN lru.track = 6 AND lru.status = 0 THEN 'Rejected by HRD'
                END as approval_status"), 'u.role', 'lru.track', 'lru.status', 'si.name', 'lru.created_at', 'lru.note')
            ->join('leave_request_update as lru', 'lr.id', '=', 'lru.id_leave_request')
            ->leftJoin('users as u', 'u.id', '=', 'lru.id_user')
            ->leftJoin('staff AS si', 'si.id', '=', 'u.id_staff')
            ->where('lr.id', $id)
            ->get();

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
                'data' => $data,
                'step' => $step
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Data Leave Permit tidak ditemukan',
                'data' => $id
            ], 404);
        }
    }

    public function cancel (Request $request, $id) {
        $input = $request->validate([
            'note' => 'required'
        ]);

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
        ->update([
            'lr.status' => 0, 
            'lre.approval' => 0, 
            'lrd.approval' => 0, 
            'lra.approval' => 0
        ]);

        DB::table('leave_request_update')
            ->insert([
                'id_leave_request' => $id,
                'id_user' => null,
                'track' => 0,
                'status' => 0,
                'created_at' => now(),
                'note' => $input['note'],  
            ]);

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

    public function cancelByHOD (Request $request, $id) {
        $input = $request->validate([
            'note' => 'required|string'
        ],[
            'note.required' => 'Note is required.',
            'note.string' => 'Note must be a string.'
        ]);

        $data = DB::table('leave_request as lr')
        ->leftJoin('leave_request_dp as lrd', 'lrd.id_leave_request', '=', 'lr.id')
        ->leftJoin('leave_request_eo as lre', 'lre.id_leave_request', '=', 'lr.id')
        ->leftJoin('leave_request_al as lra', 'lra.id_leave_request', '=', 'lr.id')
        ->leftJoin('manager_on_duty as md', 'md.id', '=', 'lrd.id_mod')
        ->leftJoin('extra_off as eo', 'eo.id', '=', 'lre.id_eo')
        ->leftJoin('annual_leave as al', 'al.id', '=', 'lra.id_al')
        ->where('lr.id', $id)
        ->select('lr.*', 'lrd.*', 'lre.*', 'lra.*', 'md.*', 'eo.*', 'al.*')
        ->update([
            'lr.status' => 0, 
            'lre.approval' => 0, 
            'lrd.approval' => 0, 
            'lra.approval' => 0
        ]);

        DB::table('leave_request_update')
            ->insert([
                'id_leave_request' => $id,
                'id_user' => Auth::user()->id,
                'track' => Auth::user()->role,
                'status' => 0,
                'note' => $input['note'],
                'created_at' => now()
            ]);

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
                'status' => 1,
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

    public function reject (Request $request, $id) {
        $input = $request->validate([
            'note' => 'required'
        ]);

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
                'status' => 0,
                'created_at' => now(),
                'note' => $input['note'],
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

    public function downloadView(Request $request){
        $id = $request->input('id');
        $ids = $request->input('ids');

        $leave = DB::table('leave_request as lr')
            ->select('lr.*')
            ->where('lr.id', $id)
            ->where('lr.status', 1)
            ->first();

        // $leave->request_date = date('l, d F Y', strtotime($leave->request_date));
        $leave->created_at = date('d-m-Y H:i', strtotime($leave->request_date));
        
        $dp = DB::table('leave_request as lr')
            ->join('leave_request_dp as lrd', 'lr.id', '=', 'lrd.id_leave_request')
            ->join('manager_on_duty as md', 'lrd.id_mod', '=', 'md.id')
            ->select('lrd.id', 'lrd.date as date_replace', 'lrd.approval', 'md.date')
            ->where('lr.id', $id)
            ->where('md.id_staff', $ids)
            ->where(function($query) {
                $query->where('lrd.approval', 2)
                    ->orWhere('lrd.approval', 1);
            })
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
            ->where(function($query) {
                $query->where('lre.approval', 2)
                    ->orWhere('lre.approval', 1);
            })
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
            ->where(function($query) {
                $query->where('lra.approval', 2)
                    ->orWhere('lra.approval', 1);
            })
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

        $staff = DB::table('staff as si')
            ->join('hr_unit as u', 'si.id_unit', '=', 'u.IdUnit')
            ->select('si.name', 'si.position', 'u.Namaunit as unit')
            ->where('si.id', $ids)
            ->first();

            $staff->position = mb_convert_case($staff->position, MB_CASE_TITLE, 'UTF-8');
            $staff->unit = mb_convert_case($staff->unit, MB_CASE_TITLE, 'UTF-8');

        $users = DB::table('leave_request_update as lru')
            ->join('users as u', 'lru.id_user', '=', 'u.id')
            ->join('staff as si', 'u.id_staff', '=', 'si.id')
            ->select('si.name', 'u.role', 'lru.created_at')
            ->where('lru.id_leave_request', $id)
            ->orderBy('lru.created_at', 'ASC')
            ->get();
        for($i = 0; $i < count($users); $i++){
            $users[$i]->name = mb_convert_case($users[$i]->name, MB_CASE_TITLE, 'UTF-8');
            $users[$i]->created_at = date('d-m-Y H:i', strtotime($users[$i]->created_at));
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
        
        return view('leave', ['data' =>$leave, 'dp' => $dp, 'eo' => $eo, 'al' => $al, 'staff' => $staff, 'id'=> $id, 'ids' => $ids, 'users' => $users, 'quota' => $quota]);
    }

    public function download(Request $request){
        $id = $request->input('id');
        $ids = $request->input('ids');
        
        $browser = (new BrowserFactory())->createBrowser([
            'windowSize' => [1920, 1080],
        ]);

    try {

        /* creates a new page and navigate to an URL */
        $page = $browser->createPage();
        $page->navigate(env('LINK')."/leave/view?id=".$id."&ids=".$ids)->waitForNavigation();
        $pageTitle = $page->evaluate('document.title')->getReturnValue();

        $options = [
            'landscape'           => true,
            'printBackground'     => false,
            'marginTop'           => 0.0, 
            'marginBottom'        => 0.0, 
            'marginLeft'          => 0.0,
            'marginRight'         => 0.0, 
            'headerTemplate'      => '<div class="grid justify-center">
            test
            </div>
        </div>',
        ];

        $name = public_path("uploads/".time().'.pdf');
        $page->pdf($options)->saveToFile($name);

        return response()->download($name);

    } finally {

        $browser->close();

    }
        
        // return Pdf::view('leave', ['data' =>$leave, 'dp' => $dp, 'eo' => $eo, 'al' => $al, 'staff' => $staff, 'id'=> $id, 'ids' => $ids, 'users' => $users, 'quota' => $quota])
        //     ->format('a4')
        //     ->margins(2, 2, 2, 2, Unit::Centimeter)
        //     ->name('leave-'.now()->format('Y-m-d').'.pdf')
        //     ->withBrowsershot(function (Browsershot $browsershot) {
        //         $browsershot->scale(1)
        //             ->setChromePath('C:\Users\henry\.cache\puppeteer\chrome\win64-123.0.6312.122\chrome-win64\chrome.exe');
        //     });
    }

    public function annualExpire () {
        $data = DB::table('annual_leave as al')
        ->select(DB::raw('Year(al.date) as year'), DB::raw('COUNT(*) as total'), DB::raw('MAX(al.expire) as expire'))
        ->groupBy(DB::raw('Year(al.date)'))
        ->orderBy(DB::raw('Year(al.date)'), 'DESC')
        ->paginate(10);

        if($data){
            return response()->json([
                'status' => true,
                'message' => 'Annual Leave expire date',
                'data' => $data
            ], 200);
        }

        return response()->json([
            'status' => false,
            'message' => 'Annual Leave expire date not found',
            'data' => null
        ], 404);
    }

    public function updateAnnualExpire(Request $request) {
        $input = $request->validate([
            'year' => 'required|integer',
            'expire' => 'required|date'
        ]);
        DB::table('annual_leave as al')
            ->where(DB::raw('Year(al.date)'), '=', $input['year'])
            ->update(['al.expire' => $input['expire']]);

        return response()->json([
            'status' => true,
            'message' => 'Annual Leave expire date has been updated',
            'data' => null
        ], 200);
    }

    public function deleteEoEntitlement (Request $request){
        $input = $request->validate([
            'id' => 'required|integer'
        ]);

        $data = DB::table('eo_entitlement')
            ->where('id', $input['id'])
            ->delete();

        if($data){
            return response()->json([
                'status' => true,
                'message' => 'Data Extra Off berhasil dihapus',
                'data' => $data
            ], 200);
        }

        return response()->json([
            'status' => false,
            'message' => 'Data Extra Off gagal dihapus',
            'data' => null
        ], 422);
    }

    public function deleteDP (Request $request) {
        $input = $request->validate([
            'id' => 'required|integer'
        ]);

        $data = DB::table('manager_on_duty')
            ->where('id', $input['id'])
            ->delete();

        if($data){
            return response()->json([
                'status' => true,
                'message' => 'Data Day Payment berhasil dihapus',
                'data' => $data
            ], 200);
        }

        return response()->json([
            'status' => false,
            'message' => 'Data Day Payment gagal dihapus',
            'data' => null
        ], 422);
    }

    public function deleteEo (Request $request) {
        $input = $request->validate([
            'id' => 'required|integer'
        ]);

        $data = DB::table('extra_off')
            ->where('id', $input['id'])
            ->delete();

        if($data){
            return response()->json([
                'status' => true,
                'message' => 'Data Extra Off berhasil dihapus',
                'data' => $data
            ], 200);
        }

        return response()->json([
            'status' => false,
            'message' => 'Data Extra Off gagal dihapus',
            'data' => null
        ], 422);
    }

    public function deleteAl (Request $request) {
        $input = $request->validate([
            'id' => 'required|integer'
        ]);

        $data = DB::table('annual_leave')
            ->where('id', $input['id'])
            ->delete();

        if($data){
            return response()->json([
                'status' => true,
                'message' => 'Data Annual Leave berhasil dihapus',
                'data' => $data
            ], 200);
        }

        return response()->json([
            'status' => false,
            'message' => 'Data Annual Leave gagal dihapus',
            'data' => null
        ], 422);
    }
}
