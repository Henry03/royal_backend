<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    // public function indexAll (Request $request) {
    //     $filter = $request->input('filter', 'Tanggal');
    //     $sort = $request->input('sort', 'desc');
    //     $fromDate = $request->input('fromDate');
    //     $toDate = $request->input('toDate');

    //     $log = DB::table('ta_log as l')
    //         ->select('DateTime', 'In_out', 'Tanggal_Log')
    //         ->whereBetween(DB::raw("STR_TO_DATE(l.DateTime, '%d/%m/%Y %H:%i:%s')"), [
    //             $fromDate.' '.'00:00:00',
    //             $toDate.' '.'00:00:00'
    //         ])
    //         ->orderBy(DB::raw("STR_TO_DATE(l.DateTime, '%d/%m/%Y %H:%i:%s')"), 'desc')
    //         ->get();

    //     $shift1 = DB::table('ta_jadwal_staffx as js')
    //         ->join('hr_staff_info as si', 'js.Fid', '=', 'si.FID')
    //         ->join('hr_unit as u', 'si.DEPT_NAME', '=', 'u.IdUnit')
    //         ->join('ta_timetable as tt', 'js.NoJadwal', '=', 'tt.ID')
    //         ->leftJoin('ta_shift as s', 'tt.Jadwal1', '=', 's.ID')
    //         ->whereNotNull('s.Nama_shift')
    //         ->whereBetween(DB::raw("STR_TO_DATE(js.Tanggal, '%d/%m/%Y')"), [
    //             $fromDate,
    //             $toDate
    //         ])
    //         ->select(
    //             'si.Nama',
    //             'u.NamaUnit',
    //             'js.Tanggal',
    //             's.Nama_shift',
    //             's.Jam_masuk',
    //             's.Jam_keluar',
    //             's.Awal_masuk',
    //             's.Awal_keluar',
    //             's.Akhir_masuk',
    //             's.Akhir_keluar'
    //         )
    //         ->get();
        
    //     $shift2 = DB::table('ta_jadwal_staffx as js')
    //         ->join('ta_timetable as tt', 'js.NoJadwal', '=', 'tt.ID')
    //         ->leftJoin('ta_shift as s', 'tt.Jadwal2', '=', 's.ID')
    //         ->whereNotNull('s.Nama_shift')
    //         ->whereBetween(DB::raw("STR_TO_DATE(js.Tanggal, '%d/%m/%Y')"), [
    //             $fromDate,
    //             $toDate
    //         ])
    //         ->select(
    //             'js.Tanggal',
    //             's.Nama_shift',
    //             's.Jam_masuk',
    //             's.Jam_keluar',
    //             's.Awal_masuk',
    //             's.Awal_keluar',
    //             's.Akhir_masuk',
    //             's.Akhir_keluar'
    //         )
    //         ->get();

    //     $shift3 = DB::table('ta_jadwal_staffx as js')
    //         ->join('ta_timetable as tt', 'js.NoJadwal', '=', 'tt.ID')
    //         ->leftJoin('ta_shift as s', 'tt.Jadwal3', '=', 's.ID')
    //         ->whereNotNull('s.Nama_shift')
    //         ->whereBetween(DB::raw("STR_TO_DATE(js.Tanggal, '%d/%m/%Y')"), [
    //             $fromDate,
    //             $toDate
    //         ])
    //         ->select(
    //             'js.Tanggal',
    //             's.Nama_shift',
    //             's.Jam_masuk',
    //             's.Jam_keluar',
    //             's.Awal_masuk',
    //             's.Awal_keluar',
    //             's.Akhir_masuk',
    //             's.Akhir_keluar'
    //         )
    //         ->get();

    //     $shift4 = DB::table('ta_jadwal_staffx as js')
    //         ->join('ta_timetable as tt', 'js.NoJadwal', '=', 'tt.ID')
    //         ->leftJoin('ta_shift as s', 'tt.Jadwal4', '=', 's.ID')
    //         ->whereNotNull('s.Nama_shift')
    //         ->whereBetween(DB::raw("STR_TO_DATE(js.Tanggal, '%d/%m/%Y')"), [
    //             $fromDate,
    //             $toDate
    //         ])
    //         ->select(
    //             'js.Tanggal',
    //             's.Nama_shift',
    //             's.Jam_masuk',
    //             's.Jam_keluar',
    //             's.Awal_masuk',
    //             's.Awal_keluar',
    //             's.Akhir_masuk',
    //             's.Akhir_keluar'
    //         )
    //         ->get();

    //     $shift5 = DB::table('ta_jadwal_staffx as js')
    //         ->join('ta_timetable as tt', 'js.NoJadwal', '=', 'tt.ID')
    //         ->leftJoin('ta_shift as s', 'tt.Jadwal5', '=', 's.ID')
    //         ->whereNotNull('s.Nama_shift')
    //         ->whereBetween(DB::raw("STR_TO_DATE(js.Tanggal, '%d/%m/%Y')"), [
    //             $fromDate,
    //             $toDate
    //         ])
    //         ->select(
    //             'js.Tanggal',
    //             's.Nama_shift',
    //             's.Jam_masuk',
    //             's.Jam_keluar',
    //             's.Awal_masuk',
    //             's.Awal_keluar',
    //             's.Akhir_masuk',
    //             's.Akhir_keluar'
    //         )
    //         ->get();

    //     // $uniqueDates = $log->pluck('Tanggal_Log')->merge($shift1->pluck('Tanggal'))->unique();
    //     $merged = [];
    //     foreach($shift1 as $shift){
    //         $timeIn = $log
    //             ->where('Tanggal_Log', $shift->Tanggal)
    //             ->filter(function ($item) use ($shift) {
    //                 $date = Carbon::createFromFormat('d/m/Y H:i:s', $item->DateTime)->format('Y-m-d H:i:s');
    //                 $startTime = Carbon::createFromFormat('d/m/Y H:i:s', $shift->Tanggal.' '.$shift->Awal_masuk.':00')->format('Y-m-d H:i:s');
    //                 $endTime = Carbon::createFromFormat('d/m/Y H:i:s', $shift->Tanggal.' '.$shift->Akhir_masuk.':00')->format('Y-m-d H:i:s');
    //                 return $item->In_out == 0 && $date >= $startTime && $date <= $endTime;
    //             })
    //             ->first();
                
    //         $timeOut = $log
    //             ->where('Tanggal_Log', $shift->Tanggal)
    //             ->filter(function ($item) use ($shift) {
    //                 $date = Carbon::createFromFormat('d/m/Y H:i:s', $item->DateTime)->format('Y-m-d H:i:s');
    //                 $startTime = Carbon::createFromFormat('d/m/Y H:i:s', $shift->Tanggal.' '.$shift->Awal_keluar.':00')->format('Y-m-d H:i:s');
    //                 $endTime = Carbon::createFromFormat('d/m/Y H:i:s', $shift->Tanggal.' '.$shift->Akhir_keluar.':00')->format('Y-m-d H:i:s');
    //                 return $item->In_out == 1 && $date >= $startTime && $date <= $endTime;
    //             })
    //             ->first();

    //         $merged[] = [
    //             'Tanggal' => Carbon::createFromFormat('d/m/Y', $shift->Tanggal)->format('Y-m-d'),
    //             'Shift' => $shift ?? null,
    //             'time_in' => $timeIn ? [
    //                 'DateTime' => Carbon::createFromFormat('d/m/Y H:i:s', $timeIn->DateTime)->format('Y-m-d H:i:s'),
    //                 'In_out' => $timeIn->In_out,
    //                 'EarlyIn' => Carbon::createFromFormat('d/m/Y H:i:s', $timeIn->DateTime)->diffInMinutes(Carbon::createFromFormat('d/m/Y H:i:s', $shift->Tanggal.' '.$shift->Jam_masuk.':00')),

    //             ] : null,
    //             'time_out' => $timeOut ? [
    //                 'DateTime' => Carbon::createFromFormat('d/m/Y H:i:s', $timeOut->DateTime)->format('Y-m-d H:i:s'),
    //                 'In_out' => $timeOut->In_out,
    //                 'EarlyOut' => Carbon::createFromFormat('d/m/Y H:i:s', $timeOut->DateTime)->diffInMinutes(Carbon::createFromFormat('d/m/Y H:i:s', $shift->Tanggal.' '.$shift->Jam_keluar.':00')),
    //             ] : null,
    //             'working_time' => 
    //                 $timeIn && $timeOut ? 
    //                 Carbon::createFromFormat('d/m/Y H:i:s', $timeIn->DateTime)
    //                     ->diff(Carbon::createFromFormat('d/m/Y H:i:s', $timeOut->DateTime))
    //                     ->format('%H:%I:%S') 
    //                 : ($timeIn ? "N/A" : ($timeOut ? "N/A" : null)),
    //         ];
            
    //     };

    //     foreach($shift2 as $shift){
    //         $timeIn = $log
    //             ->where('Tanggal_Log', $shift->Tanggal)
    //             ->filter(function ($item) use ($shift) {
    //                 $date = Carbon::createFromFormat('d/m/Y H:i:s', $item->DateTime)->format('Y-m-d H:i:s');
    //                 $startTime = Carbon::createFromFormat('d/m/Y H:i:s', $shift->Tanggal.' '.$shift->Awal_masuk.':00')->format('Y-m-d H:i:s');
    //                 $endTime = Carbon::createFromFormat('d/m/Y H:i:s', $shift->Tanggal.' '.$shift->Akhir_masuk.':00')->format('Y-m-d H:i:s');
    //                 return $item->In_out == 0 && $date >= $startTime && $date <= $endTime;
    //             })
    //             ->first();
                
    //         $timeOut = $log
    //             ->where('Tanggal_Log', $shift->Tanggal)
    //             ->filter(function ($item) use ($shift) {
    //                 $date = Carbon::createFromFormat('d/m/Y H:i:s', $item->DateTime)->format('Y-m-d H:i:s');
    //                 $startTime = Carbon::createFromFormat('d/m/Y H:i:s', $shift->Tanggal.' '.$shift->Awal_keluar.':00')->format('Y-m-d H:i:s');
    //                 $endTime = Carbon::createFromFormat('d/m/Y H:i:s', $shift->Tanggal.' '.$shift->Akhir_keluar.':00')->format('Y-m-d H:i:s');
    //                 return $item->In_out == 1 && $date >= $startTime && $date <= $endTime;
    //             })
    //             ->first();

    //         $merged[] = [
    //             'Tanggal' => Carbon::createFromFormat('d/m/Y', $shift->Tanggal)->format('Y-m-d'),
    //             'Shift' => $shift ?? null,
    //             'time_in' => $timeIn ? [
    //                 'DateTime' => Carbon::createFromFormat('d/m/Y H:i:s', $timeIn->DateTime)->format('Y-m-d H:i:s'),
    //                 'In_out' => $timeIn->In_out,
    //                 'EarlyIn' => Carbon::createFromFormat('d/m/Y H:i:s', $timeIn->DateTime)->diffInMinutes(Carbon::createFromFormat('d/m/Y H:i:s', $shift->Tanggal.' '.$shift->Jam_masuk.':00')),
    //             ] : null,
    //             'time_out' => $timeOut ? [
    //                 'DateTime' => Carbon::createFromFormat('d/m/Y H:i:s', $timeOut->DateTime)->format('Y-m-d H:i:s'),
    //                 'In_out' => $timeOut->In_out,
    //                 'EarlyOut' => Carbon::createFromFormat('d/m/Y H:i:s', $timeOut->DateTime)->diffInMinutes(Carbon::createFromFormat('d/m/Y H:i:s', $shift->Tanggal.' '.$shift->Jam_keluar.':00')),
    //             ] : null,
    //             'working_time' => $timeIn && $timeOut ? Carbon::createFromFormat('d/m/Y H:i:s', $timeIn->DateTime)->diff(Carbon::createFromFormat('d/m/Y H:i:s', $timeOut->DateTime))->format('%H:%I:%S') : null,
    //         ];
    //     };

    //     foreach($shift3 as $shift){
    //         $timeIn = $log
    //             ->where('Tanggal_Log', $shift->Tanggal)
    //             ->filter(function ($item) use ($shift) {
    //                 $date = Carbon::createFromFormat('d/m/Y H:i:s', $item->DateTime)->format('Y-m-d H:i:s');
    //                 $startTime = Carbon::createFromFormat('d/m/Y H:i:s', $shift->Tanggal.' '.$shift->Awal_masuk.':00')->format('Y-m-d H:i:s');
    //                 $endTime = Carbon::createFromFormat('d/m/Y H:i:s', $shift->Tanggal.' '.$shift->Akhir_masuk.':00')->format('Y-m-d H:i:s');
    //                 return $item->In_out == 0 && $date >= $startTime && $date <= $endTime;
    //             })
    //             ->first();
                
    //         $timeOut = $log
    //             ->where('Tanggal_Log', $shift->Tanggal)
    //             ->filter(function ($item) use ($shift) {
    //                 $date = Carbon::createFromFormat('d/m/Y H:i:s', $item->DateTime)->format('Y-m-d H:i:s');
    //                 $startTime = Carbon::createFromFormat('d/m/Y H:i:s', $shift->Tanggal.' '.$shift->Awal_keluar.':00')->format('Y-m-d H:i:s');
    //                 $endTime = Carbon::createFromFormat('d/m/Y H:i:s', $shift->Tanggal.' '.$shift->Akhir_keluar.':00')->format('Y-m-d H:i:s');
    //                 return $item->In_out == 1 && $date >= $startTime && $date <= $endTime;
    //             })
    //             ->first();
    //         $merged[] = [
    //             'Tanggal' => Carbon::createFromFormat('d/m/Y', $shift->Tanggal)->format('Y-m-d'),
    //             'Shift' => $shift ?? null,
    //             'time_in' => $timeIn ? [
    //                 'DateTime' => Carbon::createFromFormat('d/m/Y H:i:s', $timeIn->DateTime)->format('Y-m-d H:i:s'),
    //                 'In_out' => $timeIn->In_out,
    //                 'EarlyIn' => Carbon::createFromFormat('d/m/Y H:i:s', $timeIn->DateTime)->diffInMinutes(Carbon::createFromFormat('d/m/Y H:i:s', $shift->Tanggal.' '.$shift->Jam_masuk.':00')),
    //             ] : null,
    //             'time_out' => $timeOut ? [
    //                 'DateTime' => Carbon::createFromFormat('d/m/Y H:i:s', $timeOut->DateTime)->format('Y-m-d H:i:s'),
    //                 'In_out' => $timeOut->In_out,
    //                 'EarlyOut' => Carbon::createFromFormat('d/m/Y H:i:s', $timeOut->DateTime)->diffInMinutes(Carbon::createFromFormat('d/m/Y H:i:s', $shift->Tanggal.' '.$shift->Jam_keluar.':00')),
    //             ] : null,
    //             'working_time' => 
    //                 $timeIn && $timeOut ? 
    //                 Carbon::createFromFormat('d/m/Y H:i:s', $timeIn->DateTime)
    //                     ->diff(Carbon::createFromFormat('d/m/Y H:i:s', $timeOut->DateTime))
    //                     ->format('%H:%I:%S') 
    //                 : ($timeIn ? "N/A" : ($timeOut ? "N/A" : null)),
    //         ];
    //     };

    //     foreach($shift4 as $shift){
    //         $timeIn = $log
    //             ->where('Tanggal_Log', $shift->Tanggal)
    //             ->filter(function ($item) use ($shift) {
    //                 $date = Carbon::createFromFormat('d/m/Y H:i:s', $item->DateTime)->format('Y-m-d H:i:s');
    //                 $startTime = Carbon::createFromFormat('d/m/Y H:i:s', $shift->Tanggal.' '.$shift->Awal_masuk.':00')->format('Y-m-d H:i:s');
    //                 $endTime = Carbon::createFromFormat('d/m/Y H:i:s', $shift->Tanggal.' '.$shift->Akhir_masuk.':00')->format('Y-m-d H:i:s');
    //                 return $item->In_out == 0 && $date >= $startTime && $date <= $endTime;
    //             })
    //             ->first();
                
    //         $timeOut = $log
    //             ->where('Tanggal_Log', $shift->Tanggal)
    //             ->filter(function ($item) use ($shift) {
    //                 $date = Carbon::createFromFormat('d/m/Y H:i:s', $item->DateTime)->format('Y-m-d H:i:s');
    //                 $startTime = Carbon::createFromFormat('d/m/Y H:i:s', $shift->Tanggal.' '.$shift->Awal_keluar.':00')->format('Y-m-d H:i:s');
    //                 $endTime = Carbon::createFromFormat('d/m/Y H:i:s', $shift->Tanggal.' '.$shift->Akhir_keluar.':00')->format('Y-m-d H:i:s');
    //                 return $item->In_out == 1 && $date >= $startTime && $date <= $endTime;
    //             })
    //             ->first();
    //         $merged[] = [
    //             'Tanggal' => Carbon::createFromFormat('d/m/Y', $shift->Tanggal)->format('Y-m-d'),
    //             'Shift' => $shift ?? null,
    //             'time_in' => $timeIn ? [
    //                 'DateTime' => Carbon::createFromFormat('d/m/Y H:i:s', $timeIn->DateTime)->format('Y-m-d H:i:s'),
    //                 'In_out' => $timeIn->In_out,
    //                 'EarlyIn' => Carbon::createFromFormat('d/m/Y H:i:s', $timeIn->DateTime)->diffInMinutes(Carbon::createFromFormat('d/m/Y H:i:s', $shift->Tanggal.' '.$shift->Jam_masuk.':00')),
    //             ] : null,
    //             'time_out' => $timeOut ? [
    //                 'DateTime' => Carbon::createFromFormat('d/m/Y H:i:s', $timeOut->DateTime)->format('Y-m-d H:i:s'),
    //                 'In_out' => $timeOut->In_out,
    //                 'EarlyOut' => Carbon::createFromFormat('d/m/Y H:i:s', $timeOut->DateTime)->diffInMinutes(Carbon::createFromFormat('d/m/Y H:i:s', $shift->Tanggal.' '.$shift->Jam_keluar.':00')),
    //             ] : null,
    //             'working_time' => $timeIn && $timeOut ? Carbon::createFromFormat('d/m/Y H:i:s', $timeIn->DateTime)->diff(Carbon::createFromFormat('d/m/Y H:i:s', $timeOut->DateTime))->format('%H:%I:%S') : null,
    //         ];
    //     };

    //     foreach($shift5 as $shift){
    //         $timeIn = $log
    //             ->where('Tanggal_Log', $shift->Tanggal)
    //             ->filter(function ($item) use ($shift) {
    //                 $date = Carbon::createFromFormat('d/m/Y H:i:s', $item->DateTime)->format('Y-m-d H:i:s');
    //                 $startTime = Carbon::createFromFormat('d/m/Y H:i:s', $shift->Tanggal.' '.$shift->Awal_masuk.':00')->format('Y-m-d H:i:s');
    //                 $endTime = Carbon::createFromFormat('d/m/Y H:i:s', $shift->Tanggal.' '.$shift->Akhir_masuk.':00')->format('Y-m-d H:i:s');
    //                 return $item->In_out == 0 && $date >= $startTime && $date <= $endTime;
    //             })
    //             ->first();
                
    //         $timeOut = $log
    //             ->where('Tanggal_Log', $shift->Tanggal)
    //             ->filter(function ($item) use ($shift) {
    //                 $date = Carbon::createFromFormat('d/m/Y H:i:s', $item->DateTime)->format('Y-m-d H:i:s');
    //                 $startTime = Carbon::createFromFormat('d/m/Y H:i:s', $shift->Tanggal.' '.$shift->Awal_keluar.':00')->format('Y-m-d H:i:s');
    //                 $endTime = Carbon::createFromFormat('d/m/Y H:i:s', $shift->Tanggal.' '.$shift->Akhir_keluar.':00')->format('Y-m-d H:i:s');
    //                 return $item->In_out == 1 && $date >= $startTime && $date <= $endTime;
    //             })
    //             ->first();
    //         $merged[] = [
    //             'Tanggal' => Carbon::createFromFormat('d/m/Y', $shift->Tanggal)->format('Y-m-d'),
    //             'Shift' => $shift ?? null,
    //             'time_in' => $timeIn ? [
    //                 'DateTime' => Carbon::createFromFormat('d/m/Y H:i:s', $timeIn->DateTime)->format('Y-m-d H:i:s'),
    //                 'In_out' => $timeIn->In_out,
    //                 'EarlyIn' => Carbon::createFromFormat('d/m/Y H:i:s', $timeIn->DateTime)->diffInMinutes(Carbon::createFromFormat('d/m/Y H:i:s', $shift->Tanggal.' '.$shift->Jam_masuk.':00')),
    //             ] : null,
    //             'time_out' => $timeOut ? [
    //                 'DateTime' => Carbon::createFromFormat('d/m/Y H:i:s', $timeOut->DateTime)->format('Y-m-d H:i:s'),
    //                 'In_out' => $timeOut->In_out,
    //                 'EarlyOut' => Carbon::createFromFormat('d/m/Y H:i:s', $timeOut->DateTime)->diffInMinutes(Carbon::createFromFormat('d/m/Y H:i:s', $shift->Tanggal.' '.$shift->Jam_keluar.':00')),
    //             ] : null,
    //             'working_time' => $timeIn && $timeOut ? Carbon::createFromFormat('d/m/Y H:i:s', $timeIn->DateTime)->diff(Carbon::createFromFormat('d/m/Y H:i:s', $timeOut->DateTime))->format('%H:%I:%S') : null,
    //         ];
    //     };



    //     usort($merged, function ($a, $b) use ($filter, $sort) {
    //         $dateA = Carbon::createFromFormat('Y-m-d', $a[$filter]);
    //         $dateB = Carbon::createFromFormat('Y-m-d', $b[$filter]);
            
    //         if($sort == 'asc')
    //             return $dateA <=> $dateB;
    //         else if($sort == 'desc')
    //             return $dateB <=> $dateA;
    //     });
        
        

    //     $page = request()->get('page', 1);

    //     // Calculate the offset for the items on the current page
    //     $offset = ($page - 1) * 10;

    //     // Get only the items for the current page
    //     $currentPageItems = array_slice($merged, $offset, 10);

    //     // Create a LengthAwarePaginator instance
    //     $mergedPaginated = new LengthAwarePaginator(
    //         $currentPageItems,
    //         count($merged),
    //         10,
    //         $page
    //     );

    //     return response()->json([
    //         'status' => true,
    //         'message' => 'Data Attendance',
    //         'data' => $mergedPaginated,
    //         'sort' => $sort,    
    //     ], 200);
    // }

    public function indexAll (Request $request) {
        $filter = $request->input('filter', 'Tanggal');
        $sort = $request->input('sort', 'desc');
        $unit = $request->input('unit');
        $fromDate = $request->input('fromDate');
        $toDate = $request->input('toDate');
        $toDateAddDay = Carbon::createFromFormat('Y-m-d', $toDate)->addDay();
        $filterLogs = "STR_TO_DATE(l.DateTime, '%d/%m/%Y %H:%i:%s')";
        $filterShifts = "STR_TO_DATE(js.Tanggal, '%d/%m/%Y')";

        if($filter == 'date'){
            $filterLogs = "STR_TO_DATE(l.DateTime, '%d/%m/%Y %H:%i:%s')";
            $filterShifts = "STR_TO_DATE(js.Tanggal, '%d/%m/%Y')";
        }else if($filter == 'name'){
            $filterLogs = 'si.name';
            $filterShifts = 'si.name';
        }

        $logs = DB::table('ta_log as l')
            ->join('staff as si', 'l.Fid', '=', 'si.id')
            ->join('hr_unit as u', 'si.id_unit', '=', 'u.IdUnit')
            ->select(
                'Fid', 
                'Tanggal_Log', DB::raw("STR_TO_DATE(l.DateTime, '%d/%m/%Y %H:%i:%s') AS DateTime"), 
                'In_out',  
                'u.NamaUnit as unit',
                'si.id as id',
                'si.name',
            )
            ->whereBetween(DB::raw("STR_TO_DATE(l.DateTime, '%d/%m/%Y %H:%i:%s')"), [
                $fromDate.' '.'00:00:00',
                $toDateAddDay.' '.'23:59:59'
            ])
            ->where('u.IdUnit', 'like', $unit)
            ->orderBy(DB::raw($filterLogs), $sort)
            ->get();
                
        $shifts = DB::table('ta_jadwal_staffx as js')
            ->select(
                'js.Tanggal as date',
                'si.id as id',
                'si.name',
                'u.NamaUnit as unit',
                'tt.Nama_Jadwal as schedule',
                's.Jam_masuk as time_in',
                's.Jam_keluar as time_out',
                's.Awal_masuk as early_in',
                's.Awal_keluar as early_out',
                's.Akhir_masuk as late_in',
                's.Akhir_keluar as late_out',
                's.chk_besok as next_day'
            )
            ->join('staff as si', 'js.Fid', '=', 'si.id')
            ->join('hr_unit as u', 'si.id_unit', '=', 'u.IdUnit')
            ->join('ta_timetable as tt', 'js.NoJadwal', '=', 'tt.ID')
            ->leftJoin('ta_shift as s', function ($join) {
                $join->on('tt.Jadwal1', '=', 's.ID')
                    ->orOn('tt.Jadwal2', '=', 's.ID')
                    ->orOn('tt.Jadwal3', '=', 's.ID')
                    ->orOn('tt.Jadwal4', '=', 's.ID')
                    ->orOn('tt.Jadwal5', '=', 's.ID');
            })
            ->whereBetween(DB::raw("STR_TO_DATE(js.Tanggal, '%d/%m/%Y')"), [$fromDate, $toDate])
            ->where('u.IdUnit', 'like', $unit)
            ->orderBy(DB::raw($filterShifts), $sort)
            ->get();

        $merged = [];

        $logsWithoutShifts = $logs->reject(function ($log) use ($shifts) {
            return $shifts->contains(function ($shift) use ($log) {
                return $shift->date == $log->Tanggal_Log && $shift->id == $log->Fid;
            });
        });

        foreach ($shifts as $shift) {
            if($shift->next_day == '1'){
                $log = $logs->filter(function ($item) use ($shift) {
                    $tanggalLog = \Carbon\Carbon::createFromFormat('d/m/Y', $item->Tanggal_Log);
                    $startDate = \Carbon\Carbon::createFromFormat('d/m/Y', $shift->date);
                    $endDate = \Carbon\Carbon::createFromFormat('d/m/Y', $shift->date)->addDay();
                    
                    return $tanggalLog->gte($startDate) && $tanggalLog->lte($endDate) && $item->Fid == $shift->id;
                     
                });
                $logIn = $log->where('In_out', '0')
                    ->where('DateTime', '>=', Carbon::createFromFormat('d/m/Y', $shift->date)->format('Y-m-d')." ".$shift->early_in)
                    ->where('DateTime', '<=', Carbon::createFromFormat('d/m/Y', $shift->date)->format('Y-m-d')." ".$shift->late_in)
                    ->first();
                
                $logOut = $log->where('In_out', '1')
                    ->where('DateTime', '>=', Carbon::createFromFormat('d/m/Y', $shift->date)->addDay()->format('Y-m-d')." ".$shift->early_out)
                    ->where('DateTime', '<=', Carbon::createFromFormat('d/m/Y', $shift->date)->addDay()->format('Y-m-d')." ".$shift->late_out)
                    ->first();

                $timeIn = $logIn ? Carbon::createFromFormat('Y-m-d H:i:s', $logIn->DateTime) : null;
                $timeOut = $logOut ? Carbon::createFromFormat('Y-m-d H:i:s', $logOut->DateTime) : null;

                $staffLateIn = $timeIn ? $timeIn->diffInMinutes(Carbon::createFromFormat('d/m/Y H:i:s', $logIn->Tanggal_Log.' '.$shift->time_in.':00'), false) : null;
                $staffLateOut = $timeOut ? $timeOut->diffInMinutes(Carbon::createFromFormat('d/m/Y H:i:s', $logOut->Tanggal_Log.' '.$shift->time_out.':00'), false) : null;
            } else {
                $log = $logs->where('Tanggal_Log', $shift->date)->where('Fid', $shift->id);
                $logIn = $log->where('In_out', '0')
                    ->where('DateTime', '>=', Carbon::createFromFormat('d/m/Y', $shift->date)->format('Y-m-d')." ".$shift->early_in)
                    ->where('DateTime', '<=', Carbon::createFromFormat('d/m/Y', $shift->date)->format('Y-m-d')." ".$shift->late_in)
                    ->first();

                if(Carbon::createFromFormat('H:i', $shift->late_out) > Carbon::createFromFormat('H:i', '00:00') && Carbon::createFromFormat('H:i', $shift->late_out) < Carbon::createFromFormat('H:i', $shift->time_out)){
                    $logOut = $log->where('In_out', '1')
                        ->where('DateTime', '>=', Carbon::createFromFormat('d/m/Y', $shift->date)->format('Y-m-d')." ".$shift->early_out)
                        ->where('DateTime', '<=', Carbon::createFromFormat('d/m/Y', $shift->date)->addDay()->format('Y-m-d')." ".$shift->late_out)
                        ->first();

                }else {
                    $logOut = $log->where('In_out', '1')
                        ->where('DateTime', '>=', Carbon::createFromFormat('d/m/Y', $shift->date)->format('Y-m-d')." ".$shift->early_out)
                        ->where('DateTime', '<=', Carbon::createFromFormat('d/m/Y', $shift->date)->format('Y-m-d')." ".$shift->late_out)
                        ->first();
                }
                
                $timeIn = $logIn ? Carbon::createFromFormat('Y-m-d H:i:s', $logIn->DateTime) : null;
                $timeOut = $logOut ? Carbon::createFromFormat('Y-m-d H:i:s', $logOut->DateTime) : null;

                $staffLateIn = $timeIn ? $timeIn->diffInMinutes(Carbon::createFromFormat('d/m/Y H:i:s', $logIn->Tanggal_Log.' '.$shift->time_in.':00'), false) : null;
                $staffLateOut = $timeOut ? $timeOut->diffInMinutes(Carbon::createFromFormat('d/m/Y H:i:s', $logOut->Tanggal_Log.' '.$shift->time_out.':00'), false) : null;
            }

            // $logIn = $log->where('In_out', '0')->first();
            // $logOut = $log->where('In_out', '1')->first();
            



            $workingTime = null;

            if ($timeIn && $timeOut) {
                $workingTime = $timeOut->diff($timeIn)->format('%H:%I:%S');
            } elseif ($timeIn || $timeOut) {
                $workingTime = 'N/A';
            }

            $merged[] = [
                'name' => $shift->name,
                'unit' => $shift->unit,
                'date' => Carbon::createFromFormat('d/m/Y', $shift->date)->format('Y-m-d'),
                'schedule' => $shift->schedule,
                'time_in' => $shift->time_in,
                'time_out' => $shift->time_out,
                'early_in' => $shift->early_in,
                'early_out' => $shift->early_out,
                'late_in' => $shift->late_in,
                'late_out' => $shift->late_out,
                'staff_time_in' => $timeIn !=null ? $timeIn->format('Y-m-d H:i:s') : null,
                'staff_time_out' => $timeOut != null ? $timeOut->format('Y-m-d H:i:s') : null,
                'staff_late_in' => $staffLateIn,
                'staff_late_out' => $staffLateOut,
                'working_time' => $workingTime,
                'next_day' => $shift->next_day,
                'log' => $log,
                'shift_date' => $shift->date
            ];
        }

        foreach ($logsWithoutShifts as $log) {
            if(Carbon::createFromFormat('d/m/Y', $log->Tanggal_Log)->format('Y-m-d') >= $toDateAddDay->format('Y-m-d') ){
                continue;
            }
            $timeIn = null;
            $timeOut = null;
            if($log->In_out == '0'){
                $timeIn = Carbon::createFromFormat('Y-m-d H:i:s', $log->DateTime);
            } elseif($log->In_out == '1'){
                $timeOut = Carbon::createFromFormat('Y-m-d H:i:s', $log->DateTime);
            }

            $merged[] = [
                'name' => $log->name,
                'unit' => $log->unit,
                'date' => Carbon::createFromFormat('d/m/Y', $log->Tanggal_Log)->format('Y-m-d'),
                'schedule' => null, // Set to null for logs without shifts
                'time_in' => null,
                'time_out' => null,
                'early_in' => null,
                'early_out' => null,
                'late_in' => null,
                'late_out' => null,
                'log' => $log,
                'staff_time_in' => $timeIn !=null ? $timeIn->format('Y-m-d H:i:s') : null,
                'staff_time_out' => $timeOut != null ? $timeOut->format('Y-m-d H:i:s') : null,
                'working_time' => "-", // Set to null for logs without shifts
            ];
        }

        
        if($sort == 'asc'){
            usort($merged, function($a, $b) use ($filter) {
                return $a[$filter] <=> $b[$filter];
            });
        } else if($sort == 'desc'){
            usort($merged, function($a, $b) use ($filter) {
                return $b[$filter] <=> $a[$filter];
            });
        }
            
           
        // foreach ($shifts as $shift) {
        //         foreach ($logs as $log){
                
        //         if($log->Tanggal_Log == $shift->date && $log->Fid == $shift->id){
        //             // $logIn = $log->where('In_out', '0')->first();
        //             // $logOut = $log->where('In_out', '1')->first();
        //             $logIn = null;
        //             $logOut = null;
                    
        //             if ($log->In_out === '0') {
        //                 $logIn = $log;
        //             } elseif ($log->In_out === '1') {
        //                 $logOut = $log;
        //             }
        
        //             $timeIn = $logIn ? Carbon::createFromFormat('Y-m-d H:i:s', $logIn->DateTime) : null;
        //             $timeOut = $logOut ? Carbon::createFromFormat('Y-m-d H:i:s', $logOut->DateTime) : null;
        
        //             $workingTime = null;

        //             if ($timeIn && $timeOut) {
        //                 $workingTime = $timeOut->diff($timeIn)->format('%H:%I:%S');
        //             } elseif ($timeIn || $timeOut) {
        //                 $workingTime = 'N/A';
        //             }
        
        //             $merged[] = [
        //                 'name' => $log->name,
        //                 'unit' => $log->unit,
        //                 'date' => Carbon::createFromFormat('d/m/Y', $shift->date)->format('Y-m-d'),
        //                 'schedule' => $shift->schedule,
        //                 'time_in' => $shift->time_in,
        //                 'time_out' => $shift->time_out,
        //                 'early_in' => $shift->early_in,
        //                 'early_out' => $shift->early_out,
        //                 'late_in' => $shift->late_in,
        //                 'late_out' => $shift->late_out,
        //                 'staff_time_in' => $timeIn !=null ? $timeIn->format('Y-m-d H:i:s') : null,
        //                 'staff_time_out' => $timeOut != null ? $timeOut->format('Y-m-d H:i:s') : null,
        //                 'working_time' => $workingTime,
        //             ];
        //         }
        //     }
        //     // $log = $logs->where('Tanggal_Log', $shift->date)
        //     //       ->where('Fid', $shift->id);
           
        // }

        // foreach ($logs as $log) {
        //     // Check if the log entry is already included in the merged array
        //     $existingLog = collect($merged)->first(function ($item) use ($log) {
        //         return $item['date'] == $log->Tanggal_Log && $item['Fid'] == $log->Fid;
        //     });

        //     $logIn = null;
        //     $logOut = null;

        //     if ($existingLog) {
        //         $logIn = collect($existingLog)->where('In_out', '0')->first();
        //         $logOut = collect($existingLog)->where('In_out', '1')->first();
        //     }

        //     $timeIn = $logIn ? Carbon::createFromFormat('Y-m-d H:i:s', $logIn->DateTime) : null;
        //     $timeOut = $logOut ? Carbon::createFromFormat('Y-m-d H:i:s', $logOut->DateTime) : null;
        
        //     // If the log entry doesn't exist in the merged array, add it
        //     if (!$existingLog) {
        //         $merged[] = [
        //             'name' => null,
        //             'unit' => null, // Adjust this according to your log entry structure
        //             'date' => $log->Tanggal_Log,
        //             'schedule' => null, // Set to null for logs without shift time
        //             'time_in' => null,
        //             'time_out' => null,
        //             'early_in' => null,
        //             'early_out' => null,
        //             'late_in' => null,
        //             'late_out' => null,
        //             'staff_time_in' => $timeIn !=null ? $timeIn->format('Y-m-d H:i:s') : null,
        //             'staff_time_out' => $timeOut != null ? $timeOut->format('Y-m-d H:i:s') : null,
        //             'working_time' => null, // Set to null for logs without shift time
        //         ];
        //     }
        // }
        

        $page = request()->get('page', 1);
        $offset = ($page - 1) * 20;
        // Get only the items for the current page
        $currentPageItems = array_slice($merged, $offset, 20);
        // Create a LengthAwarePaginator instance
        $mergedPaginated = new LengthAwarePaginator(
            $currentPageItems,
            count($merged),
            20,
            $page
        );

        return response()->json([
            'status' => true,
            'message' => 'Data Attendance',
            'data' => $mergedPaginated,
        ], 200);
    }

    public function indexbyStaff (Request $request) {
        $filter = $request->input('filter', 'Tanggal');
        $sort = $request->input('sort', 'desc');
        $fromDate = $request->input('fromDate');
        $toDate = $request->input('toDate');
        $toDateAddDay = Carbon::createFromFormat('Y-m-d', $toDate)->addDay();
        $filterLogs = "STR_TO_DATE(l.DateTime, '%d/%m/%Y %H:%i:%s')";
        $filterShifts = "STR_TO_DATE(js.Tanggal, '%d/%m/%Y')";


        $logs = DB::table('ta_log as l')
            ->join('staff as si', 'l.Fid', '=', 'si.id')
            ->select(
                'Fid', 
                'Tanggal_Log', DB::raw("STR_TO_DATE(l.DateTime, '%d/%m/%Y %H:%i:%s') AS DateTime"), 
                'In_out',  
                'si.id as id',
                'si.name',
            )
            ->whereBetween(DB::raw("STR_TO_DATE(l.DateTime, '%d/%m/%Y %H:%i:%s')"), [
                $fromDate.' '.'00:00:00',
                $toDateAddDay.' '.'23:59:59'
            ])
            ->where('si.id', '=', Auth::user()->id_staff)
            ->orderBy(DB::raw($filterLogs), $sort)
            ->get();
                
        $shifts = DB::table('ta_jadwal_staffx as js')
            ->select(
                'js.Tanggal as date',
                'si.id as id',
                'si.name',
                'tt.Nama_Jadwal as schedule',
                's.Jam_masuk as time_in',
                's.Jam_keluar as time_out',
                's.Awal_masuk as early_in',
                's.Awal_keluar as early_out',
                's.Akhir_masuk as late_in',
                's.Akhir_keluar as late_out',
                's.chk_besok as next_day'
            )
            ->join('staff as si', 'js.Fid', '=', 'si.id')
            ->join('ta_timetable as tt', 'js.NoJadwal', '=', 'tt.ID')
            ->leftJoin('ta_shift as s', function ($join) {
                $join->on('tt.Jadwal1', '=', 's.ID')
                    ->orOn('tt.Jadwal2', '=', 's.ID')
                    ->orOn('tt.Jadwal3', '=', 's.ID')
                    ->orOn('tt.Jadwal4', '=', 's.ID')
                    ->orOn('tt.Jadwal5', '=', 's.ID');
            })
            ->whereBetween(DB::raw("STR_TO_DATE(js.Tanggal, '%d/%m/%Y')"), [$fromDate, $toDate])
            ->where('si.id', '=', Auth::user()->id_staff)
            ->orderBy(DB::raw($filterShifts), $sort)
            ->get();

        $merged = [];

        $logsWithoutShifts = $logs->reject(function ($log) use ($shifts) {
            return $shifts->contains(function ($shift) use ($log) {
                return $shift->date == $log->Tanggal_Log && $shift->id == $log->Fid;
            });
        });

        foreach ($shifts as $shift) {
            if($shift->next_day == '1'){
                $log = $logs->filter(function ($item) use ($shift) {
                    $tanggalLog = \Carbon\Carbon::createFromFormat('d/m/Y', $item->Tanggal_Log);
                    $startDate = \Carbon\Carbon::createFromFormat('d/m/Y', $shift->date);
                    $endDate = \Carbon\Carbon::createFromFormat('d/m/Y', $shift->date)->addDay();
                    
                    return $tanggalLog->gte($startDate) && $tanggalLog->lte($endDate) && $item->Fid == $shift->id;
                     
                });
                $logIn = $log->where('In_out', '0')
                    ->where('DateTime', '>=', Carbon::createFromFormat('d/m/Y', $shift->date)->format('Y-m-d')." ".$shift->early_in)
                    ->where('DateTime', '<=', Carbon::createFromFormat('d/m/Y', $shift->date)->format('Y-m-d')." ".$shift->late_in)
                    ->first();
                
                $logOut = $log->where('In_out', '1')
                    ->where('DateTime', '>=', Carbon::createFromFormat('d/m/Y', $shift->date)->addDay()->format('Y-m-d')." ".$shift->early_out)
                    ->where('DateTime', '<=', Carbon::createFromFormat('d/m/Y', $shift->date)->addDay()->format('Y-m-d')." ".$shift->late_out)
                    ->first();

                $timeIn = $logIn ? Carbon::createFromFormat('Y-m-d H:i:s', $logIn->DateTime) : null;
                $timeOut = $logOut ? Carbon::createFromFormat('Y-m-d H:i:s', $logOut->DateTime) : null;

                $staffLateIn = $timeIn ? $timeIn->diffInMinutes(Carbon::createFromFormat('d/m/Y H:i:s', $logIn->Tanggal_Log.' '.$shift->time_in.':00'), false) : null;
                $staffLateOut = $timeOut ? $timeOut->diffInMinutes(Carbon::createFromFormat('d/m/Y H:i:s', $logOut->Tanggal_Log.' '.$shift->time_out.':00'), false) : null;
            } else {
                $log = $logs->where('Tanggal_Log', $shift->date)->where('Fid', $shift->id);
                $logIn = $log->where('In_out', '0')
                    ->where('DateTime', '>=', Carbon::createFromFormat('d/m/Y', $shift->date)->format('Y-m-d')." ".$shift->early_in)
                    ->where('DateTime', '<=', Carbon::createFromFormat('d/m/Y', $shift->date)->format('Y-m-d')." ".$shift->late_in)
                    ->first();

                if(Carbon::createFromFormat('H:i', $shift->late_out) > Carbon::createFromFormat('H:i', '00:00') && Carbon::createFromFormat('H:i', $shift->late_out) < Carbon::createFromFormat('H:i', $shift->time_out)){
                    $logOut = $log->where('In_out', '1')
                        ->where('DateTime', '>=', Carbon::createFromFormat('d/m/Y', $shift->date)->format('Y-m-d')." ".$shift->early_out)
                        ->where('DateTime', '<=', Carbon::createFromFormat('d/m/Y', $shift->date)->addDay()->format('Y-m-d')." ".$shift->late_out)
                        ->first();

                }else {
                    $logOut = $log->where('In_out', '1')
                        ->where('DateTime', '>=', Carbon::createFromFormat('d/m/Y', $shift->date)->format('Y-m-d')." ".$shift->early_out)
                        ->where('DateTime', '<=', Carbon::createFromFormat('d/m/Y', $shift->date)->format('Y-m-d')." ".$shift->late_out)
                        ->first();
                }
                
                $timeIn = $logIn ? Carbon::createFromFormat('Y-m-d H:i:s', $logIn->DateTime) : null;
                $timeOut = $logOut ? Carbon::createFromFormat('Y-m-d H:i:s', $logOut->DateTime) : null;

                $staffLateIn = $timeIn ? $timeIn->diffInMinutes(Carbon::createFromFormat('d/m/Y H:i:s', $logIn->Tanggal_Log.' '.$shift->time_in.':00'), false) : null;
                $staffLateOut = $timeOut ? $timeOut->diffInMinutes(Carbon::createFromFormat('d/m/Y H:i:s', $logOut->Tanggal_Log.' '.$shift->time_out.':00'), false) : null;
            }

            // $logIn = $log->where('In_out', '0')->first();
            // $logOut = $log->where('In_out', '1')->first();
            



            $workingTime = null;

            if ($timeIn && $timeOut) {
                $workingTime = $timeOut->diff($timeIn)->format('%H:%I:%S');
            } elseif ($timeIn || $timeOut) {
                $workingTime = 'N/A';
            }

            $merged[] = [
                'name' => $shift->name,
                'date' => Carbon::createFromFormat('d/m/Y', $shift->date)->format('Y-m-d'),
                'schedule' => $shift->schedule,
                'time_in' => $shift->time_in,
                'time_out' => $shift->time_out,
                'early_in' => $shift->early_in,
                'early_out' => $shift->early_out,
                'late_in' => $shift->late_in,
                'late_out' => $shift->late_out,
                'staff_time_in' => $timeIn !=null ? $timeIn->format('Y-m-d H:i:s') : null,
                'staff_time_out' => $timeOut != null ? $timeOut->format('Y-m-d H:i:s') : null,
                'staff_late_in' => $staffLateIn,
                'staff_late_out' => $staffLateOut,
                'working_time' => $workingTime,
                'next_day' => $shift->next_day,
                'log' => $log,
                'shift_date' => $shift->date
            ];
        }

        foreach ($logsWithoutShifts as $log) {
            if(Carbon::createFromFormat('d/m/Y', $log->Tanggal_Log)->format('Y-m-d') >= $toDateAddDay->format('Y-m-d') ){
                continue;
            }
            $timeIn = null;
            $timeOut = null;
            if($log->In_out == '0'){
                $timeIn = Carbon::createFromFormat('Y-m-d H:i:s', $log->DateTime);
            } elseif($log->In_out == '1'){
                $timeOut = Carbon::createFromFormat('Y-m-d H:i:s', $log->DateTime);
            }

            $merged[] = [
                'name' => $log->name,
                'date' => Carbon::createFromFormat('d/m/Y', $log->Tanggal_Log)->format('Y-m-d'),
                'schedule' => null, // Set to null for logs without shifts
                'time_in' => null,
                'time_out' => null,
                'early_in' => null,
                'early_out' => null,
                'late_in' => null,
                'late_out' => null,
                'log' => $log,
                'staff_time_in' => $timeIn !=null ? $timeIn->format('Y-m-d H:i:s') : null,
                'staff_time_out' => $timeOut != null ? $timeOut->format('Y-m-d H:i:s') : null,
                'working_time' => "-", // Set to null for logs without shifts
            ];
        }

        
        if($sort == 'asc'){
            usort($merged, function($a, $b) use ($filter) {
                return $a[$filter] <=> $b[$filter];
            });
        } else if($sort == 'desc'){
            usort($merged, function($a, $b) use ($filter) {
                return $b[$filter] <=> $a[$filter];
            });
        }

        $page = request()->get('page', 1);
        $offset = ($page - 1) * 20;
        // Get only the items for the current page
        $currentPageItems = array_slice($merged, $offset, 20);
        // Create a LengthAwarePaginator instance
        $mergedPaginated = new LengthAwarePaginator(
            $currentPageItems,
            count($merged),
            20,
            $page
        );

        return response()->json([
            'status' => true,
            'message' => 'Data Attendance',
            'data' => $mergedPaginated,
        ], 200);
    }

    public function indexbyEmployee (Request $request) {
        $filter = $request->input('filter', 'Tanggal');
        $sort = $request->input('sort', 'desc');
        $fromDate = $request->input('fromDate');
        $toDate = $request->input('toDate');
        $toDateAddDay = Carbon::createFromFormat('Y-m-d', $toDate)->addDay();
        $filterLogs = "STR_TO_DATE(l.DateTime, '%d/%m/%Y %H:%i:%s')";
        $filterShifts = "STR_TO_DATE(js.Tanggal, '%d/%m/%Y')";


        $logs = DB::table('ta_log as l')
            ->join('staff as si', 'l.Fid', '=', 'si.id')
            ->select(
                'Fid', 
                'Tanggal_Log', DB::raw("STR_TO_DATE(l.DateTime, '%d/%m/%Y %H:%i:%s') AS DateTime"), 
                'In_out',  
                'si.id as id',
                'si.name',
            )
            ->whereBetween(DB::raw("STR_TO_DATE(l.DateTime, '%d/%m/%Y %H:%i:%s')"), [
                $fromDate.' '.'00:00:00',
                $toDateAddDay.' '.'23:59:59'
            ])
            ->where('si.id', '=', session('id_staff'))
            ->orderBy(DB::raw($filterLogs), $sort)
            ->get();
                
        $shifts = DB::table('ta_jadwal_staffx as js')
            ->select(
                'js.Tanggal as date',
                'si.id as id',
                'si.name',
                'tt.Nama_Jadwal as schedule',
                's.Jam_masuk as time_in',
                's.Jam_keluar as time_out',
                's.Awal_masuk as early_in',
                's.Awal_keluar as early_out',
                's.Akhir_masuk as late_in',
                's.Akhir_keluar as late_out',
                's.chk_besok as next_day'
            )
            ->join('staff as si', 'js.Fid', '=', 'si.id')
            ->join('ta_timetable as tt', 'js.NoJadwal', '=', 'tt.ID')
            ->leftJoin('ta_shift as s', function ($join) {
                $join->on('tt.Jadwal1', '=', 's.ID')
                    ->orOn('tt.Jadwal2', '=', 's.ID')
                    ->orOn('tt.Jadwal3', '=', 's.ID')
                    ->orOn('tt.Jadwal4', '=', 's.ID')
                    ->orOn('tt.Jadwal5', '=', 's.ID');
            })
            ->whereBetween(DB::raw("STR_TO_DATE(js.Tanggal, '%d/%m/%Y')"), [$fromDate, $toDate])
            ->where('si.id', '=', session('id_staff'))
            ->orderBy(DB::raw($filterShifts), $sort)
            ->get();

        $merged = [];

        $logsWithoutShifts = $logs->reject(function ($log) use ($shifts) {
            return $shifts->contains(function ($shift) use ($log) {
                return $shift->date == $log->Tanggal_Log && $shift->id == $log->Fid;
            });
        });

        foreach ($shifts as $shift) {
            if($shift->next_day == '1'){
                $log = $logs->filter(function ($item) use ($shift) {
                    $tanggalLog = \Carbon\Carbon::createFromFormat('d/m/Y', $item->Tanggal_Log);
                    $startDate = \Carbon\Carbon::createFromFormat('d/m/Y', $shift->date);
                    $endDate = \Carbon\Carbon::createFromFormat('d/m/Y', $shift->date)->addDay();
                    
                    return $tanggalLog->gte($startDate) && $tanggalLog->lte($endDate) && $item->Fid == $shift->id;
                     
                });
                $logIn = $log->where('In_out', '0')
                    ->where('DateTime', '>=', Carbon::createFromFormat('d/m/Y', $shift->date)->format('Y-m-d')." ".$shift->early_in)
                    ->where('DateTime', '<=', Carbon::createFromFormat('d/m/Y', $shift->date)->format('Y-m-d')." ".$shift->late_in)
                    ->first();
                
                $logOut = $log->where('In_out', '1')
                    ->where('DateTime', '>=', Carbon::createFromFormat('d/m/Y', $shift->date)->addDay()->format('Y-m-d')." ".$shift->early_out)
                    ->where('DateTime', '<=', Carbon::createFromFormat('d/m/Y', $shift->date)->addDay()->format('Y-m-d')." ".$shift->late_out)
                    ->first();

                $timeIn = $logIn ? Carbon::createFromFormat('Y-m-d H:i:s', $logIn->DateTime) : null;
                $timeOut = $logOut ? Carbon::createFromFormat('Y-m-d H:i:s', $logOut->DateTime) : null;

                $staffLateIn = $timeIn ? $timeIn->diffInMinutes(Carbon::createFromFormat('d/m/Y H:i:s', $logIn->Tanggal_Log.' '.$shift->time_in.':00'), false) : null;
                $staffLateOut = $timeOut ? $timeOut->diffInMinutes(Carbon::createFromFormat('d/m/Y H:i:s', $logOut->Tanggal_Log.' '.$shift->time_out.':00'), false) : null;
            } else {
                $log = $logs->where('Tanggal_Log', $shift->date)->where('Fid', $shift->id);
                $logIn = $log->where('In_out', '0')
                    ->where('DateTime', '>=', Carbon::createFromFormat('d/m/Y', $shift->date)->format('Y-m-d')." ".$shift->early_in)
                    ->where('DateTime', '<=', Carbon::createFromFormat('d/m/Y', $shift->date)->format('Y-m-d')." ".$shift->late_in)
                    ->first();

                if(Carbon::createFromFormat('H:i', $shift->late_out) > Carbon::createFromFormat('H:i', '00:00') && Carbon::createFromFormat('H:i', $shift->late_out) < Carbon::createFromFormat('H:i', $shift->time_out)){
                    $logOut = $log->where('In_out', '1')
                        ->where('DateTime', '>=', Carbon::createFromFormat('d/m/Y', $shift->date)->format('Y-m-d')." ".$shift->early_out)
                        ->where('DateTime', '<=', Carbon::createFromFormat('d/m/Y', $shift->date)->addDay()->format('Y-m-d')." ".$shift->late_out)
                        ->first();

                }else {
                    $logOut = $log->where('In_out', '1')
                        ->where('DateTime', '>=', Carbon::createFromFormat('d/m/Y', $shift->date)->format('Y-m-d')." ".$shift->early_out)
                        ->where('DateTime', '<=', Carbon::createFromFormat('d/m/Y', $shift->date)->format('Y-m-d')." ".$shift->late_out)
                        ->first();
                }
                
                $timeIn = $logIn ? Carbon::createFromFormat('Y-m-d H:i:s', $logIn->DateTime) : null;
                $timeOut = $logOut ? Carbon::createFromFormat('Y-m-d H:i:s', $logOut->DateTime) : null;

                $staffLateIn = $timeIn ? $timeIn->diffInMinutes(Carbon::createFromFormat('d/m/Y H:i:s', $logIn->Tanggal_Log.' '.$shift->time_in.':00'), false) : null;
                $staffLateOut = $timeOut ? $timeOut->diffInMinutes(Carbon::createFromFormat('d/m/Y H:i:s', $logOut->Tanggal_Log.' '.$shift->time_out.':00'), false) : null;
            }

            // $logIn = $log->where('In_out', '0')->first();
            // $logOut = $log->where('In_out', '1')->first();
            



            $workingTime = null;

            if ($timeIn && $timeOut) {
                $workingTime = $timeOut->diff($timeIn)->format('%H:%I:%S');
            } elseif ($timeIn || $timeOut) {
                $workingTime = 'N/A';
            }

            $merged[] = [
                'name' => $shift->name,
                'date' => Carbon::createFromFormat('d/m/Y', $shift->date)->format('Y-m-d'),
                'schedule' => $shift->schedule,
                'time_in' => $shift->time_in,
                'time_out' => $shift->time_out,
                'early_in' => $shift->early_in,
                'early_out' => $shift->early_out,
                'late_in' => $shift->late_in,
                'late_out' => $shift->late_out,
                'staff_time_in' => $timeIn !=null ? $timeIn->format('Y-m-d H:i:s') : null,
                'staff_time_out' => $timeOut != null ? $timeOut->format('Y-m-d H:i:s') : null,
                'staff_late_in' => $staffLateIn,
                'staff_late_out' => $staffLateOut,
                'working_time' => $workingTime,
                'next_day' => $shift->next_day,
                'log' => $log,
                'shift_date' => $shift->date
            ];
        }

        foreach ($logsWithoutShifts as $log) {
            if(Carbon::createFromFormat('d/m/Y', $log->Tanggal_Log)->format('Y-m-d') >= $toDateAddDay->format('Y-m-d') ){
                continue;
            }
            $timeIn = null;
            $timeOut = null;
            if($log->In_out == '0'){
                $timeIn = Carbon::createFromFormat('Y-m-d H:i:s', $log->DateTime);
            } elseif($log->In_out == '1'){
                $timeOut = Carbon::createFromFormat('Y-m-d H:i:s', $log->DateTime);
            }

            $merged[] = [
                'name' => $log->name,
                'date' => Carbon::createFromFormat('d/m/Y', $log->Tanggal_Log)->format('Y-m-d'),
                'schedule' => null, // Set to null for logs without shifts
                'time_in' => null,
                'time_out' => null,
                'early_in' => null,
                'early_out' => null,
                'late_in' => null,
                'late_out' => null,
                'log' => $log,
                'staff_time_in' => $timeIn !=null ? $timeIn->format('Y-m-d H:i:s') : null,
                'staff_time_out' => $timeOut != null ? $timeOut->format('Y-m-d H:i:s') : null,
                'working_time' => "-", // Set to null for logs without shifts
            ];
        }

        
        if($sort == 'asc'){
            usort($merged, function($a, $b) use ($filter) {
                return $a[$filter] <=> $b[$filter];
            });
        } else if($sort == 'desc'){
            usort($merged, function($a, $b) use ($filter) {
                return $b[$filter] <=> $a[$filter];
            });
        }

        $page = request()->get('page', 1);
        $offset = ($page - 1) * 20;
        // Get only the items for the current page
        $currentPageItems = array_slice($merged, $offset, 20);
        // Create a LengthAwarePaginator instance
        $mergedPaginated = new LengthAwarePaginator(
            $currentPageItems,
            count($merged),
            20,
            $page
        );

        return response()->json([
            'status' => true,
            'message' => 'Data Attendance',
            'data' => $mergedPaginated,
        ], 200);
    }

    
}
