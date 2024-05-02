<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ManagerOnDutyController extends Controller
{
    public function indexbyDeparment (Request $request) {
        $startDate = $request->startDate;
        $endDate = $request->endDate;

        $user = DB::table('users')
            ->join('staff', 'users.id_staff', '=', 'staff.id')
            ->where('id_staff', '=', Auth::user()->id_staff)
            ->first();
        
            
        $data = DB::table('manager_on_duty as mod')
            ->join('staff as si', 'si.id', '=', 'mod.id_staff')
            ->select('si.id as FID', 'si.name', 'si.position', 'mod.date', 'mod.type', 'mod.id')
            ->where('si.id_unit', '=', $user->DEPT_NAME)
            ->whereDate('mod.date', '>=', $startDate)
            ->whereDate('mod.date', '<=', $endDate)
            ->get();

        $groupedData = [];

        foreach ($data as $item) {
            $idStaff = $item->FID; // Replace 'ID' with the actual property of your data
            $idMod = $item->id; // Replace 'ID' with the actual property of your data
            $name = $item->name; // Replace 'Nama' with the actual property of your data
            $position = $item->position; // Replace 'JABATAN' with the actual property of your data
            $date = $item->date; // Replace 'date' with the actual property of your data
            $type = $item->type; // Replace 'type' with the actual property of your data
        
            $existingEntry = array_filter($groupedData, function ($entry) use ($idStaff) {
                return $entry['id'] == $idStaff;
            });

            if($type == 'MOD'){
                $color = 'rgb(34,197,94)';
            } else {
                $color = 'rgb(34,34,197)';
            }
        
            if (empty($existingEntry)) {
                // If not, create a new entry
                $groupedData[] = [
                    'id' => $idStaff,
                    'label' => [
                        'icon' => 'https://daisyui.com/images/stock/photo-1534528741775-53994a69daeb.jpg', // Replace with the actual icon value
                        'title' => $name,
                        'subtitle' => $position,
                    ],
                    'data' => [
                        [
                            'id' => $idMod,
                            'description' => $date,
                            'startDate' => $date,
                            'endDate' => $date,
                            'title' => $type,
                            'bgColor' => $color
                        ],
                    ],
                ];
            } else {
                // If exists, append the data to the existing entry
                $groupedData[key($existingEntry)]['data'][] = [
                    'id' => $idMod,
                    'description' => $date,
                    'startDate' => $date,
                    'endDate' => $date,
                    'title' => $type,
                    'bgColor' => $color
                ];
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'Data Manager On Duty',
            'data' => $groupedData
        ], 200);
    }

    public function indexbyDeparmentMod (Request $request) {
        $startDate = $request->startDate;
        $endDate = $request->endDate;
        $filter = $request->input('filter', 'date');
        $sort = $request->input('Fid', 'desc');

        $user = DB::table('users')
            ->join('staff', 'users.id_staff', '=', 'staff.id')
            ->where('id_staff', '=', Auth::user()->id_staff)
            ->first();
        
        if($filter == 'date'){
            $filter == "STR_TO_DATE(mod.date, '%Y-%m-%d')";
        }
        
        $data = DB::table('manager_on_duty as mod')
        ->join('staff as si', 'si.id', '=', 'mod.id_staff')
        ->select('si.id as Fid', 'si.Nama', 'si.JABATAN', 'mod.date', 'mod.type', 'mod.id')
        ->where('si.id_unit', '=', $user->DEPT_NAME)
        ->whereDate('mod.date', '>=', $startDate)
        ->whereDate('mod.date', '<=', $endDate)
        ->orderByRaw("$filter $sort")
        ->paginate(10);

        return response()->json([
            'status' => true,
            'message' => 'Data Manager On Duty',
            'data' => $data
        ], 200);
    }

    public function store (Request $request) {
        $input = $request->validate([
            'id_staff' => 'required',
            'date' => 'required',
            'type' => 'required'
        ]);

        $input['expire'] = date('Y-m-d H:i:s', strtotime($input['date'] . ' + 30 day'));

        $result = DB::table('manager_on_duty')
            ->insert($input);

        if ($result) {
            return response()->json([
                'status' => true,
                'message' => 'Data Manager On Duty berhasil ditambahkan',
                'data' => $input
            ], 200);
        } else {
        return response()->json([
            'status' => false,
            'message' => 'Data Manager On Duty gagal ditambahkan',
            'data' => $input
        ], 500);
        }
    }

    public function show (Request $request, $id) {

        $result = DB::table('manager_on_duty as mod')
            ->join('staff as si', 'si.id', '=', 'mod.id_staff')
            ->select('si.id', 'si.name as Nama', 'si.position as JABATAN', 'mod.date', 'mod.type', 'mod.id')
            ->where('mod.id', '=', $id)
            ->first();

        return response()->json([
            'status' => true,
            'message' => 'Data Manager On Duty',
            'data' => $result
        ], 200);
    }

    public function calendar(Request $request){
        $date = $request->date;
        $start = Carbon::parse($date)->firstOfMonth(); // Replace with your start date
        $end = Carbon::parse($date)->endOfMonth();   // Replace with your end date

        $dateRange = [];
        while ($start->lte($end)) {
            $dateRange[] = $start->toDateString();
            $start->addDay(); // You can use addWeek(), addMonth(), etc., depending on your needs
        }

        $user = DB::table('users')
            ->join('staff', 'users.id_staff', '=', 'staff.id')
            ->where('id_staff', '=', Auth::user()->id_staff)
            ->where(function ($query) {
                $query->whereNull('staff.status')
                    ->orWhere('staff.status', 'Active');
            })
            ->first();
        
            
        $mod = DB::table('staff AS si')
            ->leftJoin(DB::raw("(SELECT m.date, m.type, m.id, m.id_staff FROM manager_on_duty AS m WHERE DATE_FORMAT(m.date, '%Y-%m') = '$date') AS md"), 'si.id', '=', 'md.id_staff')
            ->select('si.id as id_staff', 'si.name', 'si.position', 'md.date', 'md.type', 'md.id')
            ->where('si.id_unit', $user->id_unit)
            ->where(function ($query) {
                $query->whereNull('si.status')
                    ->orWhere('si.status', 'Active');
            })
            ->orderBy('si.name', 'ASC')
            ->get();

        $calendar = DB::table('ta_hari_libur')
            ->where(DB::raw("DATE_FORMAT(STR_TO_DATE(tgl_libur, '%d/%m/%Y'), '%Y-%m')"), '=', $date)
            ->select('Nama_libur as name', DB::raw('STR_TO_DATE(tgl_libur, "%d/%m/%Y") as date'))
            ->orderBy(DB::raw("STR_TO_DATE(tgl_libur, '%d/%m/%Y')"), 'ASC')
            ->get();

        $scheduleList = DB::table('ta_timetable as tt')
            ->select('tt.ID as id', 'tt.Nama_Jadwal as name')
            ->get();

        $schedule = DB::table('ta_jadwal_staffx as js')
            ->join('ta_timetable as tt', 'js.NoJadwal', '=', 'tt.ID')
            ->join('staff as si', 'si.id', '=', 'js.Fid')
            ->select('js.Fid as id_staff', 'js.Tanggal as date', 'tt.ID as id', 'tt.Nama_Jadwal as schedule_name')
            ->where('si.id_unit', '=', $user->id_unit)
            ->where(function ($query) use ($date) {
                $query->where(DB::raw("DATE_FORMAT(str_to_date(js.Tanggal, '%d/%m/%Y'), '%Y-%m')"), '=', $date);
            })
            ->where(function ($query) {
                $query->whereNull('si.status')
                    ->orWhere('si.status', 'Active');
            })
            ->get();

        $dp = DB::table('leave_request_dp as dp')
            ->join('manager_on_duty as md', 'md.id', '=', 'dp.id_mod')
            ->join('staff as si', 'si.id', '=', 'md.id_staff')
            ->select('md.id_staff', 'dp.date', 'md.id')
            ->where('si.id_unit', '=', $user->id_unit)
            ->where(function ($query) use ($date) {
                $query->where(DB::raw("DATE_FORMAT(dp.date, '%Y-%m')"), '=', $date);
            })
            ->where(function ($query) {
                $query->whereNull('si.status')
                    ->orWhere('si.status', 'Active');
            })
            ->where('dp.approval', '=', '2')
            ->get();

        $eo = DB::table('leave_request_eo as lre')
            ->join('extra_off as eo', 'eo.id', '=', 'lre.id_eo')
            ->join('staff as si', 'si.id', '=', 'eo.id_staff')
            ->select('eo.id_staff', 'lre.date', 'eo.id')
            ->where('si.id_unit', '=', $user->id_unit)
            ->where(function ($query) use ($date) {
                $query->where(DB::raw("DATE_FORMAT(eo.date, '%Y-%m')"), '=', $date);
            })
            ->where(function ($query) {
                $query->whereNull('si.status')
                    ->orWhere('si.status', 'Active');
            })
            ->where('lre.approval', '=', '2')
            ->get();

        $al = DB::table('leave_request_al as lra')
            ->join('annual_leave as al', 'al.id', '=', 'lra.id_al')
            ->join('staff as si', 'si.id', '=', 'al.id_staff')
            ->select('al.id_staff', 'lra.date', 'lra.id')
            ->where('si.id_unit', '=', $user->id_unit)
            ->where(function ($query) use ($date) {
                $query->where(DB::raw("DATE_FORMAT(lra.date, '%Y-%m')"), '=', $date);
            })
            ->where(function ($query) {
                $query->whereNull('si.status')
                    ->orWhere('si.status', 'Active');
            })
            ->where('lra.approval', '=', '2')
            ->get();

        // $shift1 = $result = DB::table('ta_timetable as tt')
        // ->leftJoin('ta_shift as ts', 'ts.ID', '=', 'tt.Jadwal1')
        // ->select('tt.ID as id', 'tt.Nama_Jadwal as schedule_name', 'ts.Nama_Shift as shift_name', 'ts.Jam_masuk as time_in', 'ts.Jam_keluar as time_out')
        // ->get();

        // $shift2 = DB::table('ta_timetable as tt')
        // ->leftJoin('ta_shift as ts', 'ts.ID', '=', 'tt.Jadwal1')
        // ->select('tt.ID as id', 'tt.Nama_Jadwal as schedule_name', 'ts.Nama_Shift as shift_name', 'ts.Jam_masuk as time_in', 'ts.Jam_keluar as time_out')
        // ->get();

        // $shift3 = DB::table('ta_timetable as tt')
        // ->leftJoin('ta_shift as ts', 'ts.ID', '=', 'tt.Jadwal1')
        // ->select('tt.ID as id', 'tt.Nama_Jadwal as schedule_name', 'ts.Nama_Shift as shift_name', 'ts.Jam_masuk as time_in', 'ts.Jam_keluar as time_out')
        // ->get();

        // $shift4 = DB::table('ta_timetable as tt')
        // ->leftJoin('ta_shift as ts', 'ts.ID', '=', 'tt.Jadwal4')
        // ->select('tt.ID as id', 'tt.Nama_Jadwal as schedule_name', 'ts.Nama_Shift as shift_name', 'ts.Jam_masuk as time_in', 'ts.Jam_keluar as time_out')
        // ->get();

        // $shift5 = DB::table('ta_timetable as tt')
        // ->leftJoin('ta_shift as ts', 'ts.ID', '=', 'tt.Jadwal5')
        // ->select('tt.ID as id', 'tt.Nama_Jadwal as schedule_name', 'ts.Nama_Shift as shift_name', 'ts.Jam_masuk as time_in', 'ts.Jam_keluar as time_out')
        // ->get();


        
        $mergedData = [];

        foreach ($mod as $entry) {
            $id = $entry->id_staff;

            if (!isset($mergedData[$id])) {
                $mergedData[$id] = [
                    'id' =>$entry->id_staff,
                    'name' => $entry->name,
                    'position' => $entry->position,
                    'data' => [],
                ];
            }

            $mergedData[$id]['data'][] = [
                'id' => $entry->id,
                'date' => $entry->date,
                'type' => $entry->type,
            ];
        }

        // return response()->json([
        //     'status' => true,
        //     'message' => 'Calendar Data',
        //     'mergedData' => $mod,
        // ], 200);

        foreach ($schedule as $entry) {
            $id = $entry->id_staff;
            $date = Carbon::createFromFormat('d/m/Y', $entry->date)->format('Y-m-d');

            // Check if the date already exists in mergedData
            $existingData = $mergedData[$id]['data'];
            $existingDateKeys = array_column($existingData, 'date');
            $key = array_search($date, $existingDateKeys);

            if ($key !== false) {
                // If the date exists, merge the data
                $mergedData[$id]['data'][$key] = array_merge(
                    $mergedData[$id]['data'][$key],
                    [
                        'shift' => $entry->schedule_name,
                    ]
                );
            } else {
                // If the date does not exist, add the data
                $mergedData[$id]['data'][] = [
                    'id' => $entry->id,
                    'date' => $date,
                    'type' => $entry->id,
                ];
            }
        }

        foreach ($dp as $entry) {
            $id = $entry->id_staff;

            $mergedData[$id]['data'][] = [
                'id' => $entry->id,
                'date' => $entry->date,
                'type' => 'DP',
            ];
        }

        foreach ($eo as $entry) {
            $id = $entry->id_staff;

            $mergedData[$id]['data'][] = [
                'id' => $entry->id,
                'date' => $entry->date,
                'type' => 'EO',
            ];
        }

        foreach ($al as $entry) {
            $id = $entry->id_staff;

            $mergedData[$id]['data'][] = [
                'id' => $entry->id,
                'date' => $entry->date,
                'type' => 'AL',
            ];
        }

        foreach ($dateRange as $date) {
            foreach ($mergedData as $key => $value) {
                $existingEntry = array_filter($value['data'], function ($entry) use ($date) {
                    return $entry['date'] == $date;
                });

                if (empty($existingEntry)) {
                    $mergedData[$key]['data'][] = [
                        'id' => null,
                        'date' => $date,
                        'type' => null,
                    ];
                }
            }
        }

        $mergedData = array_values($mergedData);

        return response()->json([
            'status' => true,
            'message' => 'Calendar Data',
            'data' => $dateRange,
            'mergedData' => $mergedData,
            'calendar' => $calendar,
            'schedule' => $scheduleList,
        ], 200);
    }

    public function update (Request $request) {
        $input = $request->validate([
            'id_staff' => 'required',
            'date' => 'required',
            'type' => 'required'
        ]);

        $mod = DB::table('manager_on_duty')
            ->where('id', $request->id)
            ->first();

        $shift = DB::table('ta_jadwal_staffx')
            ->where('Fid', $request->id_staff)
            ->whereRaw("STR_TO_DATE(Tanggal, '%d/%m/%Y') = ?", [$request->date])
            ->first();

        if($mod){
            DB::table('manager_on_duty')
                ->where('id', $request->id)
                ->delete();
        }else if($shift){
            DB::table('ta_jadwal_staffx')
                ->where('Fid', $request->id_staff)
                ->whereRaw("STR_TO_DATE(Tanggal, '%d/%m/%Y') = ?", [$request->date])
                ->delete();
        }

        if($input['type'] == 'MOD' || $input['type'] == 'Incharge') {
            $result = DB::table('ta_jadwal_staffx')
                ->updateOrInsert(['Fid' => $request->id_staff, 'Tanggal' => Carbon::parse($request->date)->format('d/m/Y')], ['NoJadwal' => $request->modShift]);
            $input['expire'] = date('Y-m-d H:i:s', strtotime($input['date'] . ' + 30 day'));
            $result = DB::table('manager_on_duty')
                ->updateOrInsert(['id' => $request->id], $input);
        } else {
            $result = DB::table('ta_jadwal_staffx')
                ->updateOrInsert(['Fid' => $request->id_staff, 'Tanggal' => Carbon::parse($request->date)->format('d/m/Y')], ['NoJadwal' => $request->type]);
        }


        if ($result) {
            return response()->json([
                'status' => true,
                'message' => 'Data Manager On Duty berhasil diubah',
                'data' => $input
            ], 200);
        }

        return response()->json([
            'status' => false,
            'message' => 'Data Manager On Duty gagal diubah',
            'data' => $input
        ], 500);
    }

    public function destroy (Request $request) {
        $input = $request->validate([
            'id' => 'required',
            'id_staff' => 'required',
            'type' => 'required',
            'date' => 'required',
        ]);

        if($input['type'] == 'MOD' || $input['type'] == 'Incharge') {
            $result = DB::table('manager_on_duty')
                ->where('id', $input['id'])
                ->delete();
        } else {
            $result = DB::table('ta_jadwal_staffx')
                ->where('Fid', $input['id_staff'])
                ->whereRaw("STR_TO_DATE(Tanggal, '%d/%m/%Y') = ?", [$input['date']])
                ->delete();
        }

        if ($result) {
            return response()->json([
                'status' => true,
                'message' => 'Schedule unset successfully',
            ], 200);
        }

        return response()->json([
            'status' => false,
            'message' => 'Failed to unset schedule',
        ], 422);
    }
}
