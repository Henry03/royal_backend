<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;

class AttendanceController extends Controller
{
    public function indexAll (Request $request) {
        $filter = $request->input('filter', 'Tanggal');
        $sort = $request->input('sort', 'desc');
        $fromDate = $request->input('fromDate');
        $toDate = $request->input('toDate');

        $log = DB::table('ta_log as l')
            ->select('DateTime', 'In_out', 'Tanggal_Log')
            ->whereBetween(DB::raw("STR_TO_DATE(l.DateTime, '%d/%m/%Y %H:%i:%s')"), [
                $fromDate.' '.'00:00:00',
                $toDate.' '.'00:00:00'
            ])
            ->orderBy(DB::raw("STR_TO_DATE(l.DateTime, '%d/%m/%Y %H:%i:%s')"), 'desc')
            ->get();

        $shift1 = DB::table('ta_jadwal_staffx as js')
            ->join('hr_staff_info as si', 'js.Fid', '=', 'si.FID')
            ->join('hr_unit as u', 'si.DEPT_NAME', '=', 'u.IdUnit')
            ->join('ta_timetable as tt', 'js.NoJadwal', '=', 'tt.ID')
            ->leftJoin('ta_shift as s', 'tt.Jadwal1', '=', 's.ID')
            ->whereNotNull('s.Nama_shift')
            ->whereBetween(DB::raw("STR_TO_DATE(js.Tanggal, '%d/%m/%Y')"), [
                $fromDate,
                $toDate
            ])
            ->select(
                'si.Nama',
                'u.NamaUnit',
                'js.Tanggal',
                's.Nama_shift',
                's.Jam_masuk',
                's.Jam_keluar',
                's.Awal_masuk',
                's.Awal_keluar',
                's.Akhir_masuk',
                's.Akhir_keluar'
            )
            ->get();
        
        $shift2 = DB::table('ta_jadwal_staffx as js')
            ->join('ta_timetable as tt', 'js.NoJadwal', '=', 'tt.ID')
            ->leftJoin('ta_shift as s', 'tt.Jadwal2', '=', 's.ID')
            ->whereNotNull('s.Nama_shift')
            ->whereBetween(DB::raw("STR_TO_DATE(js.Tanggal, '%d/%m/%Y')"), [
                $fromDate,
                $toDate
            ])
            ->select(
                'js.Tanggal',
                's.Nama_shift',
                's.Jam_masuk',
                's.Jam_keluar',
                's.Awal_masuk',
                's.Awal_keluar',
                's.Akhir_masuk',
                's.Akhir_keluar'
            )
            ->get();

        $shift3 = DB::table('ta_jadwal_staffx as js')
            ->join('ta_timetable as tt', 'js.NoJadwal', '=', 'tt.ID')
            ->leftJoin('ta_shift as s', 'tt.Jadwal3', '=', 's.ID')
            ->whereNotNull('s.Nama_shift')
            ->whereBetween(DB::raw("STR_TO_DATE(js.Tanggal, '%d/%m/%Y')"), [
                $fromDate,
                $toDate
            ])
            ->select(
                'js.Tanggal',
                's.Nama_shift',
                's.Jam_masuk',
                's.Jam_keluar',
                's.Awal_masuk',
                's.Awal_keluar',
                's.Akhir_masuk',
                's.Akhir_keluar'
            )
            ->get();

        $shift4 = DB::table('ta_jadwal_staffx as js')
            ->join('ta_timetable as tt', 'js.NoJadwal', '=', 'tt.ID')
            ->leftJoin('ta_shift as s', 'tt.Jadwal4', '=', 's.ID')
            ->whereNotNull('s.Nama_shift')
            ->whereBetween(DB::raw("STR_TO_DATE(js.Tanggal, '%d/%m/%Y')"), [
                $fromDate,
                $toDate
            ])
            ->select(
                'js.Tanggal',
                's.Nama_shift',
                's.Jam_masuk',
                's.Jam_keluar',
                's.Awal_masuk',
                's.Awal_keluar',
                's.Akhir_masuk',
                's.Akhir_keluar'
            )
            ->get();

        $shift5 = DB::table('ta_jadwal_staffx as js')
            ->join('ta_timetable as tt', 'js.NoJadwal', '=', 'tt.ID')
            ->leftJoin('ta_shift as s', 'tt.Jadwal5', '=', 's.ID')
            ->whereNotNull('s.Nama_shift')
            ->whereBetween(DB::raw("STR_TO_DATE(js.Tanggal, '%d/%m/%Y')"), [
                $fromDate,
                $toDate
            ])
            ->select(
                'js.Tanggal',
                's.Nama_shift',
                's.Jam_masuk',
                's.Jam_keluar',
                's.Awal_masuk',
                's.Awal_keluar',
                's.Akhir_masuk',
                's.Akhir_keluar'
            )
            ->get();

        // $uniqueDates = $log->pluck('Tanggal_Log')->merge($shift1->pluck('Tanggal'))->unique();
        $merged = [];
        foreach($shift1 as $shift){
            $timeIn = $log
                ->where('Tanggal_Log', $shift->Tanggal)
                ->filter(function ($item) use ($shift) {
                    $date = Carbon::createFromFormat('d/m/Y H:i:s', $item->DateTime)->format('Y-m-d H:i:s');
                    $startTime = Carbon::createFromFormat('d/m/Y H:i:s', $shift->Tanggal.' '.$shift->Awal_masuk.':00')->format('Y-m-d H:i:s');
                    $endTime = Carbon::createFromFormat('d/m/Y H:i:s', $shift->Tanggal.' '.$shift->Akhir_masuk.':00')->format('Y-m-d H:i:s');
                    return $item->In_out == 0 && $date >= $startTime && $date <= $endTime;
                })
                ->first();
                
            $timeOut = $log
                ->where('Tanggal_Log', $shift->Tanggal)
                ->filter(function ($item) use ($shift) {
                    $date = Carbon::createFromFormat('d/m/Y H:i:s', $item->DateTime)->format('Y-m-d H:i:s');
                    $startTime = Carbon::createFromFormat('d/m/Y H:i:s', $shift->Tanggal.' '.$shift->Awal_keluar.':00')->format('Y-m-d H:i:s');
                    $endTime = Carbon::createFromFormat('d/m/Y H:i:s', $shift->Tanggal.' '.$shift->Akhir_keluar.':00')->format('Y-m-d H:i:s');
                    return $item->In_out == 1 && $date >= $startTime && $date <= $endTime;
                })
                ->first();

            $merged[] = [
                'Tanggal' => Carbon::createFromFormat('d/m/Y', $shift->Tanggal)->format('Y-m-d'),
                'Shift' => $shift ?? null,
                'time_in' => $timeIn ? [
                    'DateTime' => Carbon::createFromFormat('d/m/Y H:i:s', $timeIn->DateTime)->format('Y-m-d H:i:s'),
                    'In_out' => $timeIn->In_out,
                    'EarlyIn' => Carbon::createFromFormat('d/m/Y H:i:s', $timeIn->DateTime)->diffInMinutes(Carbon::createFromFormat('d/m/Y H:i:s', $shift->Tanggal.' '.$shift->Jam_masuk.':00')),

                ] : null,
                'time_out' => $timeOut ? [
                    'DateTime' => Carbon::createFromFormat('d/m/Y H:i:s', $timeOut->DateTime)->format('Y-m-d H:i:s'),
                    'In_out' => $timeOut->In_out,
                    'EarlyOut' => Carbon::createFromFormat('d/m/Y H:i:s', $timeOut->DateTime)->diffInMinutes(Carbon::createFromFormat('d/m/Y H:i:s', $shift->Tanggal.' '.$shift->Jam_keluar.':00')),
                ] : null,
                'working_time' => 
                    $timeIn && $timeOut ? 
                    Carbon::createFromFormat('d/m/Y H:i:s', $timeIn->DateTime)
                        ->diff(Carbon::createFromFormat('d/m/Y H:i:s', $timeOut->DateTime))
                        ->format('%H:%I:%S') 
                    : ($timeIn ? "N/A" : ($timeOut ? "N/A" : null)),
            ];
            
        };

        foreach($shift2 as $shift){
            $timeIn = $log
                ->where('Tanggal_Log', $shift->Tanggal)
                ->filter(function ($item) use ($shift) {
                    $date = Carbon::createFromFormat('d/m/Y H:i:s', $item->DateTime)->format('Y-m-d H:i:s');
                    $startTime = Carbon::createFromFormat('d/m/Y H:i:s', $shift->Tanggal.' '.$shift->Awal_masuk.':00')->format('Y-m-d H:i:s');
                    $endTime = Carbon::createFromFormat('d/m/Y H:i:s', $shift->Tanggal.' '.$shift->Akhir_masuk.':00')->format('Y-m-d H:i:s');
                    return $item->In_out == 0 && $date >= $startTime && $date <= $endTime;
                })
                ->first();
                
            $timeOut = $log
                ->where('Tanggal_Log', $shift->Tanggal)
                ->filter(function ($item) use ($shift) {
                    $date = Carbon::createFromFormat('d/m/Y H:i:s', $item->DateTime)->format('Y-m-d H:i:s');
                    $startTime = Carbon::createFromFormat('d/m/Y H:i:s', $shift->Tanggal.' '.$shift->Awal_keluar.':00')->format('Y-m-d H:i:s');
                    $endTime = Carbon::createFromFormat('d/m/Y H:i:s', $shift->Tanggal.' '.$shift->Akhir_keluar.':00')->format('Y-m-d H:i:s');
                    return $item->In_out == 1 && $date >= $startTime && $date <= $endTime;
                })
                ->first();

            $merged[] = [
                'Tanggal' => Carbon::createFromFormat('d/m/Y', $shift->Tanggal)->format('Y-m-d'),
                'Shift' => $shift ?? null,
                'time_in' => $timeIn ? [
                    'DateTime' => Carbon::createFromFormat('d/m/Y H:i:s', $timeIn->DateTime)->format('Y-m-d H:i:s'),
                    'In_out' => $timeIn->In_out,
                    'EarlyIn' => Carbon::createFromFormat('d/m/Y H:i:s', $timeIn->DateTime)->diffInMinutes(Carbon::createFromFormat('d/m/Y H:i:s', $shift->Tanggal.' '.$shift->Jam_masuk.':00')),
                ] : null,
                'time_out' => $timeOut ? [
                    'DateTime' => Carbon::createFromFormat('d/m/Y H:i:s', $timeOut->DateTime)->format('Y-m-d H:i:s'),
                    'In_out' => $timeOut->In_out,
                    'EarlyOut' => Carbon::createFromFormat('d/m/Y H:i:s', $timeOut->DateTime)->diffInMinutes(Carbon::createFromFormat('d/m/Y H:i:s', $shift->Tanggal.' '.$shift->Jam_keluar.':00')),
                ] : null,
                'working_time' => $timeIn && $timeOut ? Carbon::createFromFormat('d/m/Y H:i:s', $timeIn->DateTime)->diff(Carbon::createFromFormat('d/m/Y H:i:s', $timeOut->DateTime))->format('%H:%I:%S') : null,
            ];
        };

        foreach($shift3 as $shift){
            $timeIn = $log
                ->where('Tanggal_Log', $shift->Tanggal)
                ->filter(function ($item) use ($shift) {
                    $date = Carbon::createFromFormat('d/m/Y H:i:s', $item->DateTime)->format('Y-m-d H:i:s');
                    $startTime = Carbon::createFromFormat('d/m/Y H:i:s', $shift->Tanggal.' '.$shift->Awal_masuk.':00')->format('Y-m-d H:i:s');
                    $endTime = Carbon::createFromFormat('d/m/Y H:i:s', $shift->Tanggal.' '.$shift->Akhir_masuk.':00')->format('Y-m-d H:i:s');
                    return $item->In_out == 0 && $date >= $startTime && $date <= $endTime;
                })
                ->first();
                
            $timeOut = $log
                ->where('Tanggal_Log', $shift->Tanggal)
                ->filter(function ($item) use ($shift) {
                    $date = Carbon::createFromFormat('d/m/Y H:i:s', $item->DateTime)->format('Y-m-d H:i:s');
                    $startTime = Carbon::createFromFormat('d/m/Y H:i:s', $shift->Tanggal.' '.$shift->Awal_keluar.':00')->format('Y-m-d H:i:s');
                    $endTime = Carbon::createFromFormat('d/m/Y H:i:s', $shift->Tanggal.' '.$shift->Akhir_keluar.':00')->format('Y-m-d H:i:s');
                    return $item->In_out == 1 && $date >= $startTime && $date <= $endTime;
                })
                ->first();
            $merged[] = [
                'Tanggal' => Carbon::createFromFormat('d/m/Y', $shift->Tanggal)->format('Y-m-d'),
                'Shift' => $shift ?? null,
                'time_in' => $timeIn ? [
                    'DateTime' => Carbon::createFromFormat('d/m/Y H:i:s', $timeIn->DateTime)->format('Y-m-d H:i:s'),
                    'In_out' => $timeIn->In_out,
                    'EarlyIn' => Carbon::createFromFormat('d/m/Y H:i:s', $timeIn->DateTime)->diffInMinutes(Carbon::createFromFormat('d/m/Y H:i:s', $shift->Tanggal.' '.$shift->Jam_masuk.':00')),
                ] : null,
                'time_out' => $timeOut ? [
                    'DateTime' => Carbon::createFromFormat('d/m/Y H:i:s', $timeOut->DateTime)->format('Y-m-d H:i:s'),
                    'In_out' => $timeOut->In_out,
                    'EarlyOut' => Carbon::createFromFormat('d/m/Y H:i:s', $timeOut->DateTime)->diffInMinutes(Carbon::createFromFormat('d/m/Y H:i:s', $shift->Tanggal.' '.$shift->Jam_keluar.':00')),
                ] : null,
                'working_time' => 
                    $timeIn && $timeOut ? 
                    Carbon::createFromFormat('d/m/Y H:i:s', $timeIn->DateTime)
                        ->diff(Carbon::createFromFormat('d/m/Y H:i:s', $timeOut->DateTime))
                        ->format('%H:%I:%S') 
                    : ($timeIn ? "N/A" : ($timeOut ? "N/A" : null)),
            ];
        };

        foreach($shift4 as $shift){
            $timeIn = $log
                ->where('Tanggal_Log', $shift->Tanggal)
                ->filter(function ($item) use ($shift) {
                    $date = Carbon::createFromFormat('d/m/Y H:i:s', $item->DateTime)->format('Y-m-d H:i:s');
                    $startTime = Carbon::createFromFormat('d/m/Y H:i:s', $shift->Tanggal.' '.$shift->Awal_masuk.':00')->format('Y-m-d H:i:s');
                    $endTime = Carbon::createFromFormat('d/m/Y H:i:s', $shift->Tanggal.' '.$shift->Akhir_masuk.':00')->format('Y-m-d H:i:s');
                    return $item->In_out == 0 && $date >= $startTime && $date <= $endTime;
                })
                ->first();
                
            $timeOut = $log
                ->where('Tanggal_Log', $shift->Tanggal)
                ->filter(function ($item) use ($shift) {
                    $date = Carbon::createFromFormat('d/m/Y H:i:s', $item->DateTime)->format('Y-m-d H:i:s');
                    $startTime = Carbon::createFromFormat('d/m/Y H:i:s', $shift->Tanggal.' '.$shift->Awal_keluar.':00')->format('Y-m-d H:i:s');
                    $endTime = Carbon::createFromFormat('d/m/Y H:i:s', $shift->Tanggal.' '.$shift->Akhir_keluar.':00')->format('Y-m-d H:i:s');
                    return $item->In_out == 1 && $date >= $startTime && $date <= $endTime;
                })
                ->first();
            $merged[] = [
                'Tanggal' => Carbon::createFromFormat('d/m/Y', $shift->Tanggal)->format('Y-m-d'),
                'Shift' => $shift ?? null,
                'time_in' => $timeIn ? [
                    'DateTime' => Carbon::createFromFormat('d/m/Y H:i:s', $timeIn->DateTime)->format('Y-m-d H:i:s'),
                    'In_out' => $timeIn->In_out,
                    'EarlyIn' => Carbon::createFromFormat('d/m/Y H:i:s', $timeIn->DateTime)->diffInMinutes(Carbon::createFromFormat('d/m/Y H:i:s', $shift->Tanggal.' '.$shift->Jam_masuk.':00')),
                ] : null,
                'time_out' => $timeOut ? [
                    'DateTime' => Carbon::createFromFormat('d/m/Y H:i:s', $timeOut->DateTime)->format('Y-m-d H:i:s'),
                    'In_out' => $timeOut->In_out,
                    'EarlyOut' => Carbon::createFromFormat('d/m/Y H:i:s', $timeOut->DateTime)->diffInMinutes(Carbon::createFromFormat('d/m/Y H:i:s', $shift->Tanggal.' '.$shift->Jam_keluar.':00')),
                ] : null,
                'working_time' => $timeIn && $timeOut ? Carbon::createFromFormat('d/m/Y H:i:s', $timeIn->DateTime)->diff(Carbon::createFromFormat('d/m/Y H:i:s', $timeOut->DateTime))->format('%H:%I:%S') : null,
            ];
        };

        foreach($shift5 as $shift){
            $timeIn = $log
                ->where('Tanggal_Log', $shift->Tanggal)
                ->filter(function ($item) use ($shift) {
                    $date = Carbon::createFromFormat('d/m/Y H:i:s', $item->DateTime)->format('Y-m-d H:i:s');
                    $startTime = Carbon::createFromFormat('d/m/Y H:i:s', $shift->Tanggal.' '.$shift->Awal_masuk.':00')->format('Y-m-d H:i:s');
                    $endTime = Carbon::createFromFormat('d/m/Y H:i:s', $shift->Tanggal.' '.$shift->Akhir_masuk.':00')->format('Y-m-d H:i:s');
                    return $item->In_out == 0 && $date >= $startTime && $date <= $endTime;
                })
                ->first();
                
            $timeOut = $log
                ->where('Tanggal_Log', $shift->Tanggal)
                ->filter(function ($item) use ($shift) {
                    $date = Carbon::createFromFormat('d/m/Y H:i:s', $item->DateTime)->format('Y-m-d H:i:s');
                    $startTime = Carbon::createFromFormat('d/m/Y H:i:s', $shift->Tanggal.' '.$shift->Awal_keluar.':00')->format('Y-m-d H:i:s');
                    $endTime = Carbon::createFromFormat('d/m/Y H:i:s', $shift->Tanggal.' '.$shift->Akhir_keluar.':00')->format('Y-m-d H:i:s');
                    return $item->In_out == 1 && $date >= $startTime && $date <= $endTime;
                })
                ->first();
            $merged[] = [
                'Tanggal' => Carbon::createFromFormat('d/m/Y', $shift->Tanggal)->format('Y-m-d'),
                'Shift' => $shift ?? null,
                'time_in' => $timeIn ? [
                    'DateTime' => Carbon::createFromFormat('d/m/Y H:i:s', $timeIn->DateTime)->format('Y-m-d H:i:s'),
                    'In_out' => $timeIn->In_out,
                    'EarlyIn' => Carbon::createFromFormat('d/m/Y H:i:s', $timeIn->DateTime)->diffInMinutes(Carbon::createFromFormat('d/m/Y H:i:s', $shift->Tanggal.' '.$shift->Jam_masuk.':00')),
                ] : null,
                'time_out' => $timeOut ? [
                    'DateTime' => Carbon::createFromFormat('d/m/Y H:i:s', $timeOut->DateTime)->format('Y-m-d H:i:s'),
                    'In_out' => $timeOut->In_out,
                    'EarlyOut' => Carbon::createFromFormat('d/m/Y H:i:s', $timeOut->DateTime)->diffInMinutes(Carbon::createFromFormat('d/m/Y H:i:s', $shift->Tanggal.' '.$shift->Jam_keluar.':00')),
                ] : null,
                'working_time' => $timeIn && $timeOut ? Carbon::createFromFormat('d/m/Y H:i:s', $timeIn->DateTime)->diff(Carbon::createFromFormat('d/m/Y H:i:s', $timeOut->DateTime))->format('%H:%I:%S') : null,
            ];
        };



        usort($merged, function ($a, $b) use ($filter, $sort) {
            $dateA = Carbon::createFromFormat('Y-m-d', $a[$filter]);
            $dateB = Carbon::createFromFormat('Y-m-d', $b[$filter]);
            
            if($sort == 'asc')
                return $dateA <=> $dateB;
            else if($sort == 'desc')
                return $dateB <=> $dateA;
        });
        
        

        $page = request()->get('page', 1);

        // Calculate the offset for the items on the current page
        $offset = ($page - 1) * 10;

        // Get only the items for the current page
        $currentPageItems = array_slice($merged, $offset, 10);

        // Create a LengthAwarePaginator instance
        $mergedPaginated = new LengthAwarePaginator(
            $currentPageItems,
            count($merged),
            10,
            $page
        );

        return response()->json([
            'status' => true,
            'message' => 'Data Attendance',
            'data' => $mergedPaginated,
            'sort' => $sort,    
        ], 200);
    }

    public function indexbyEmployee (Request $request) {
        $filter = $request->input('filter', 'Tanggal');
        $sort = $request->input('sort', 'desc');
        $fromDate = $request->input('fromDate');
        $toDate = $request->input('toDate');

        $id = session('id_staff');

        $log = DB::table('ta_log as l')
            ->select('DateTime', 'In_out', 'Tanggal_Log')
            ->where('l.Fid', $id)
            ->whereBetween(DB::raw("STR_TO_DATE(l.DateTime, '%d/%m/%Y %H:%i:%s')"), [
                $fromDate.' '.'00:00:00',
                $toDate.' '.'00:00:00'
            ])
            ->orderBy(DB::raw("STR_TO_DATE(l.DateTime, '%d/%m/%Y %H:%i:%s')"), 'desc')
            ->get();

        $shift1 = DB::table('ta_jadwal_staffx as js')
            ->join('ta_timetable as tt', 'js.NoJadwal', '=', 'tt.ID')
            ->leftJoin('ta_shift as s', 'tt.Jadwal1', '=', 's.ID')
            ->where('js.Fid', $id)
            ->whereNotNull('s.Nama_shift')
            ->whereBetween(DB::raw("STR_TO_DATE(js.Tanggal, '%d/%m/%Y')"), [
                $fromDate,
                $toDate
            ])
            ->select(
                'js.Tanggal',
                's.Nama_shift',
                's.Jam_masuk',
                's.Jam_keluar',
                's.Awal_masuk',
                's.Awal_keluar',
                's.Akhir_masuk',
                's.Akhir_keluar'
            )
            ->orderBy(DB::raw("STR_TO_DATE(js.Tanggal, '%d/%m/%Y')"), 'desc')
            ->get();
        
        
        $shift2 = DB::table('ta_jadwal_staffx as js')
            ->join('ta_timetable as tt', 'js.NoJadwal', '=', 'tt.ID')
            ->leftJoin('ta_shift as s', 'tt.Jadwal2', '=', 's.ID')
            ->where('js.Fid', $id)
            ->whereNotNull('s.Nama_shift')
            ->whereBetween(DB::raw("STR_TO_DATE(js.Tanggal, '%d/%m/%Y')"), [
                $fromDate,
                $toDate
            ])
            ->select(
                'js.Tanggal',
                's.Nama_shift',
                's.Jam_masuk',
                's.Jam_keluar',
                's.Awal_masuk',
                's.Awal_keluar',
                's.Akhir_masuk',
                's.Akhir_keluar'
            )
            ->orderBy(DB::raw("STR_TO_DATE(js.Tanggal, '%d/%m/%Y')"), 'desc')
            ->get();

        $shift3 = DB::table('ta_jadwal_staffx as js')
            ->join('ta_timetable as tt', 'js.NoJadwal', '=', 'tt.ID')
            ->leftJoin('ta_shift as s', 'tt.Jadwal3', '=', 's.ID')
            ->where('js.Fid', $id)
            ->whereNotNull('s.Nama_shift')
            ->whereBetween(DB::raw("STR_TO_DATE(js.Tanggal, '%d/%m/%Y')"), [
                $fromDate,
                $toDate
            ])
            ->select(
                'js.Tanggal',
                's.Nama_shift',
                's.Jam_masuk',
                's.Jam_keluar',
                's.Awal_masuk',
                's.Awal_keluar',
                's.Akhir_masuk',
                's.Akhir_keluar'
            )
            ->orderBy(DB::raw("STR_TO_DATE(js.Tanggal, '%d/%m/%Y')"), 'desc')
            ->get();

        $shift4 = DB::table('ta_jadwal_staffx as js')
            ->join('ta_timetable as tt', 'js.NoJadwal', '=', 'tt.ID')
            ->leftJoin('ta_shift as s', 'tt.Jadwal4', '=', 's.ID')
            ->where('js.Fid', $id)
            ->whereNotNull('s.Nama_shift')
            ->whereBetween(DB::raw("STR_TO_DATE(js.Tanggal, '%d/%m/%Y')"), [
                $fromDate,
                $toDate
            ])
            ->select(
                'js.Tanggal',
                's.Nama_shift',
                's.Jam_masuk',
                's.Jam_keluar',
                's.Awal_masuk',
                's.Awal_keluar',
                's.Akhir_masuk',
                's.Akhir_keluar'
            )
            ->orderBy(DB::raw("STR_TO_DATE(js.Tanggal, '%d/%m/%Y')"), 'desc')
            ->get();

        $shift5 = DB::table('ta_jadwal_staffx as js')
            ->join('ta_timetable as tt', 'js.NoJadwal', '=', 'tt.ID')
            ->leftJoin('ta_shift as s', 'tt.Jadwal5', '=', 's.ID')
            ->where('js.Fid', $id)
            ->whereNotNull('s.Nama_shift')
            ->whereBetween(DB::raw("STR_TO_DATE(js.Tanggal, '%d/%m/%Y')"), [
                $fromDate,
                $toDate
            ])
            ->select(
                'js.Tanggal',
                's.Nama_shift',
                's.Jam_masuk',
                's.Jam_keluar',
                's.Awal_masuk',
                's.Awal_keluar',
                's.Akhir_masuk',
                's.Akhir_keluar'
            )
            ->orderBy(DB::raw("STR_TO_DATE(js.Tanggal, '%d/%m/%Y')"), 'desc')
            ->get();


        // $uniqueDates = $log->pluck('Tanggal_Log')->merge($shift1->pluck('Tanggal'))->unique();
        $merged = [];
        foreach($shift1 as $shift){
            $timeIn = $log
                ->where('Tanggal_Log', $shift->Tanggal)
                ->filter(function ($item) use ($shift) {
                    $date = Carbon::createFromFormat('d/m/Y H:i:s', $item->DateTime)->format('Y-m-d H:i:s');
                    $startTime = Carbon::createFromFormat('d/m/Y H:i:s', $shift->Tanggal.' '.$shift->Awal_masuk.':00')->format('Y-m-d H:i:s');
                    $endTime = Carbon::createFromFormat('d/m/Y H:i:s', $shift->Tanggal.' '.$shift->Akhir_masuk.':00')->format('Y-m-d H:i:s');
                    return $item->In_out == 0 && $date >= $startTime && $date <= $endTime;
                })
                ->first();
                
            $timeOut = $log
                ->where('Tanggal_Log', $shift->Tanggal)
                ->filter(function ($item) use ($shift) {
                    $date = Carbon::createFromFormat('d/m/Y H:i:s', $item->DateTime)->format('Y-m-d H:i:s');
                    $startTime = Carbon::createFromFormat('d/m/Y H:i:s', $shift->Tanggal.' '.$shift->Awal_keluar.':00')->format('Y-m-d H:i:s');
                    $endTime = Carbon::createFromFormat('d/m/Y H:i:s', $shift->Tanggal.' '.$shift->Akhir_keluar.':00')->format('Y-m-d H:i:s');
                    return $item->In_out == 1 && $date >= $startTime && $date <= $endTime;
                })
                ->first();

            $merged[] = [
                'Tanggal' => Carbon::createFromFormat('d/m/Y', $shift->Tanggal)->format('Y-m-d'),
                'Shift' => $shift ?? null,
                'time_in' => $timeIn ? [
                    'DateTime' => Carbon::createFromFormat('d/m/Y H:i:s', $timeIn->DateTime)->format('Y-m-d H:i:s'),
                    'In_out' => $timeIn->In_out,
                    'EarlyIn' => Carbon::createFromFormat('d/m/Y H:i:s', $timeIn->DateTime)->diffInMinutes(Carbon::createFromFormat('d/m/Y H:i:s', $shift->Tanggal.' '.$shift->Jam_masuk.':00')),

                ] : null,
                'time_out' => $timeOut ? [
                    'DateTime' => Carbon::createFromFormat('d/m/Y H:i:s', $timeOut->DateTime)->format('Y-m-d H:i:s'),
                    'In_out' => $timeOut->In_out,
                    'EarlyOut' => Carbon::createFromFormat('d/m/Y H:i:s', $timeOut->DateTime)->diffInMinutes(Carbon::createFromFormat('d/m/Y H:i:s', $shift->Tanggal.' '.$shift->Jam_keluar.':00')),
                ] : null,
                'working_time' => $timeIn && $timeOut ? Carbon::createFromFormat('d/m/Y H:i:s', $timeIn->DateTime)->diff(Carbon::createFromFormat('d/m/Y H:i:s', $timeOut->DateTime))->format('%H:%I:%S') : null,
            ];
            
        };

        foreach($shift2 as $shift){
            $timeIn = $log
                ->where('Tanggal_Log', $shift->Tanggal)
                ->filter(function ($item) use ($shift) {
                    $date = Carbon::createFromFormat('d/m/Y H:i:s', $item->DateTime)->format('Y-m-d H:i:s');
                    $startTime = Carbon::createFromFormat('d/m/Y H:i:s', $shift->Tanggal.' '.$shift->Awal_masuk.':00')->format('Y-m-d H:i:s');
                    $endTime = Carbon::createFromFormat('d/m/Y H:i:s', $shift->Tanggal.' '.$shift->Akhir_masuk.':00')->format('Y-m-d H:i:s');
                    return $item->In_out == 0 && $date >= $startTime && $date <= $endTime;
                })
                ->first();
                
            $timeOut = $log
                ->where('Tanggal_Log', $shift->Tanggal)
                ->filter(function ($item) use ($shift) {
                    $date = Carbon::createFromFormat('d/m/Y H:i:s', $item->DateTime)->format('Y-m-d H:i:s');
                    $startTime = Carbon::createFromFormat('d/m/Y H:i:s', $shift->Tanggal.' '.$shift->Awal_keluar.':00')->format('Y-m-d H:i:s');
                    $endTime = Carbon::createFromFormat('d/m/Y H:i:s', $shift->Tanggal.' '.$shift->Akhir_keluar.':00')->format('Y-m-d H:i:s');
                    return $item->In_out == 1 && $date >= $startTime && $date <= $endTime;
                })
                ->first();

            $merged[] = [
                'Tanggal' => Carbon::createFromFormat('d/m/Y', $shift->Tanggal)->format('Y-m-d'),
                'Shift' => $shift ?? null,
                'time_in' => $timeIn ? [
                    'DateTime' => Carbon::createFromFormat('d/m/Y H:i:s', $timeIn->DateTime)->format('Y-m-d H:i:s'),
                    'In_out' => $timeIn->In_out,
                    'EarlyIn' => Carbon::createFromFormat('d/m/Y H:i:s', $timeIn->DateTime)->diffInMinutes(Carbon::createFromFormat('d/m/Y H:i:s', $shift->Tanggal.' '.$shift->Jam_masuk.':00')),
                ] : null,
                'time_out' => $timeOut ? [
                    'DateTime' => Carbon::createFromFormat('d/m/Y H:i:s', $timeOut->DateTime)->format('Y-m-d H:i:s'),
                    'In_out' => $timeOut->In_out,
                    'EarlyOut' => Carbon::createFromFormat('d/m/Y H:i:s', $timeOut->DateTime)->diffInMinutes(Carbon::createFromFormat('d/m/Y H:i:s', $shift->Tanggal.' '.$shift->Jam_keluar.':00')),
                ] : null,
                'working_time' => $timeIn && $timeOut ? Carbon::createFromFormat('d/m/Y H:i:s', $timeIn->DateTime)->diff(Carbon::createFromFormat('d/m/Y H:i:s', $timeOut->DateTime))->format('%H:%I:%S') : null,
            ];
        };

        foreach($shift3 as $shift){
            $timeIn = $log
                ->where('Tanggal_Log', $shift->Tanggal)
                ->filter(function ($item) use ($shift) {
                    $date = Carbon::createFromFormat('d/m/Y H:i:s', $item->DateTime)->format('Y-m-d H:i:s');
                    $startTime = Carbon::createFromFormat('d/m/Y H:i:s', $shift->Tanggal.' '.$shift->Awal_masuk.':00')->format('Y-m-d H:i:s');
                    $endTime = Carbon::createFromFormat('d/m/Y H:i:s', $shift->Tanggal.' '.$shift->Akhir_masuk.':00')->format('Y-m-d H:i:s');
                    return $item->In_out == 0 && $date >= $startTime && $date <= $endTime;
                })
                ->first();
                
            $timeOut = $log
                ->where('Tanggal_Log', $shift->Tanggal)
                ->filter(function ($item) use ($shift) {
                    $date = Carbon::createFromFormat('d/m/Y H:i:s', $item->DateTime)->format('Y-m-d H:i:s');
                    $startTime = Carbon::createFromFormat('d/m/Y H:i:s', $shift->Tanggal.' '.$shift->Awal_keluar.':00')->format('Y-m-d H:i:s');
                    $endTime = Carbon::createFromFormat('d/m/Y H:i:s', $shift->Tanggal.' '.$shift->Akhir_keluar.':00')->format('Y-m-d H:i:s');
                    return $item->In_out == 1 && $date >= $startTime && $date <= $endTime;
                })
                ->first();
            $merged[] = [
                'Tanggal' => Carbon::createFromFormat('d/m/Y', $shift->Tanggal)->format('Y-m-d'),
                'Shift' => $shift ?? null,
                'time_in' => $timeIn ? [
                    'DateTime' => Carbon::createFromFormat('d/m/Y H:i:s', $timeIn->DateTime)->format('Y-m-d H:i:s'),
                    'In_out' => $timeIn->In_out,
                    'EarlyIn' => Carbon::createFromFormat('d/m/Y H:i:s', $timeIn->DateTime)->diffInMinutes(Carbon::createFromFormat('d/m/Y H:i:s', $shift->Tanggal.' '.$shift->Jam_masuk.':00')),
                ] : null,
                'time_out' => $timeOut ? [
                    'DateTime' => Carbon::createFromFormat('d/m/Y H:i:s', $timeOut->DateTime)->format('Y-m-d H:i:s'),
                    'In_out' => $timeOut->In_out,
                    'EarlyOut' => Carbon::createFromFormat('d/m/Y H:i:s', $timeOut->DateTime)->diffInMinutes(Carbon::createFromFormat('d/m/Y H:i:s', $shift->Tanggal.' '.$shift->Jam_keluar.':00')),
                ] : null,
                'working_time' => $timeIn && $timeOut ? Carbon::createFromFormat('d/m/Y H:i:s', $timeIn->DateTime)->diff(Carbon::createFromFormat('d/m/Y H:i:s', $timeOut->DateTime))->format('%H:%I:%S') : null,
            ];
        };

        foreach($shift4 as $shift){
            $timeIn = $log
                ->where('Tanggal_Log', $shift->Tanggal)
                ->filter(function ($item) use ($shift) {
                    $date = Carbon::createFromFormat('d/m/Y H:i:s', $item->DateTime)->format('Y-m-d H:i:s');
                    $startTime = Carbon::createFromFormat('d/m/Y H:i:s', $shift->Tanggal.' '.$shift->Awal_masuk.':00')->format('Y-m-d H:i:s');
                    $endTime = Carbon::createFromFormat('d/m/Y H:i:s', $shift->Tanggal.' '.$shift->Akhir_masuk.':00')->format('Y-m-d H:i:s');
                    return $item->In_out == 0 && $date >= $startTime && $date <= $endTime;
                })
                ->first();
                
            $timeOut = $log
                ->where('Tanggal_Log', $shift->Tanggal)
                ->filter(function ($item) use ($shift) {
                    $date = Carbon::createFromFormat('d/m/Y H:i:s', $item->DateTime)->format('Y-m-d H:i:s');
                    $startTime = Carbon::createFromFormat('d/m/Y H:i:s', $shift->Tanggal.' '.$shift->Awal_keluar.':00')->format('Y-m-d H:i:s');
                    $endTime = Carbon::createFromFormat('d/m/Y H:i:s', $shift->Tanggal.' '.$shift->Akhir_keluar.':00')->format('Y-m-d H:i:s');
                    return $item->In_out == 1 && $date >= $startTime && $date <= $endTime;
                })
                ->first();
            $merged[] = [
                'Tanggal' => Carbon::createFromFormat('d/m/Y', $shift->Tanggal)->format('Y-m-d'),
                'Shift' => $shift ?? null,
                'time_in' => $timeIn ? [
                    'DateTime' => Carbon::createFromFormat('d/m/Y H:i:s', $timeIn->DateTime)->format('Y-m-d H:i:s'),
                    'In_out' => $timeIn->In_out,
                    'EarlyIn' => Carbon::createFromFormat('d/m/Y H:i:s', $timeIn->DateTime)->diffInMinutes(Carbon::createFromFormat('d/m/Y H:i:s', $shift->Tanggal.' '.$shift->Jam_masuk.':00')),
                ] : null,
                'time_out' => $timeOut ? [
                    'DateTime' => Carbon::createFromFormat('d/m/Y H:i:s', $timeOut->DateTime)->format('Y-m-d H:i:s'),
                    'In_out' => $timeOut->In_out,
                    'EarlyOut' => Carbon::createFromFormat('d/m/Y H:i:s', $timeOut->DateTime)->diffInMinutes(Carbon::createFromFormat('d/m/Y H:i:s', $shift->Tanggal.' '.$shift->Jam_keluar.':00')),
                ] : null,
                'working_time' => $timeIn && $timeOut ? Carbon::createFromFormat('d/m/Y H:i:s', $timeIn->DateTime)->diff(Carbon::createFromFormat('d/m/Y H:i:s', $timeOut->DateTime))->format('%H:%I:%S') : null,
            ];
        };

        foreach($shift5 as $shift){
            $timeIn = $log
                ->where('Tanggal_Log', $shift->Tanggal)
                ->filter(function ($item) use ($shift) {
                    $date = Carbon::createFromFormat('d/m/Y H:i:s', $item->DateTime)->format('Y-m-d H:i:s');
                    $startTime = Carbon::createFromFormat('d/m/Y H:i:s', $shift->Tanggal.' '.$shift->Awal_masuk.':00')->format('Y-m-d H:i:s');
                    $endTime = Carbon::createFromFormat('d/m/Y H:i:s', $shift->Tanggal.' '.$shift->Akhir_masuk.':00')->format('Y-m-d H:i:s');
                    return $item->In_out == 0 && $date >= $startTime && $date <= $endTime;
                })
                ->first();
                
            $timeOut = $log
                ->where('Tanggal_Log', $shift->Tanggal)
                ->filter(function ($item) use ($shift) {
                    $date = Carbon::createFromFormat('d/m/Y H:i:s', $item->DateTime)->format('Y-m-d H:i:s');
                    $startTime = Carbon::createFromFormat('d/m/Y H:i:s', $shift->Tanggal.' '.$shift->Awal_keluar.':00')->format('Y-m-d H:i:s');
                    $endTime = Carbon::createFromFormat('d/m/Y H:i:s', $shift->Tanggal.' '.$shift->Akhir_keluar.':00')->format('Y-m-d H:i:s');
                    return $item->In_out == 1 && $date >= $startTime && $date <= $endTime;
                })
                ->first();
            $merged[] = [
                'Tanggal' => Carbon::createFromFormat('d/m/Y', $shift->Tanggal)->format('Y-m-d'),
                'Shift' => $shift ?? null,
                'time_in' => $timeIn ? [
                    'DateTime' => Carbon::createFromFormat('d/m/Y H:i:s', $timeIn->DateTime)->format('Y-m-d H:i:s'),
                    'In_out' => $timeIn->In_out,
                    'EarlyIn' => Carbon::createFromFormat('d/m/Y H:i:s', $timeIn->DateTime)->diffInMinutes(Carbon::createFromFormat('d/m/Y H:i:s', $shift->Tanggal.' '.$shift->Jam_masuk.':00')),
                ] : null,
                'time_out' => $timeOut ? [
                    'DateTime' => Carbon::createFromFormat('d/m/Y H:i:s', $timeOut->DateTime)->format('Y-m-d H:i:s'),
                    'In_out' => $timeOut->In_out,
                    'EarlyOut' => Carbon::createFromFormat('d/m/Y H:i:s', $timeOut->DateTime)->diffInMinutes(Carbon::createFromFormat('d/m/Y H:i:s', $shift->Tanggal.' '.$shift->Jam_keluar.':00')),
                ] : null,
                'working_time' => $timeIn && $timeOut ? Carbon::createFromFormat('d/m/Y H:i:s', $timeIn->DateTime)->diff(Carbon::createFromFormat('d/m/Y H:i:s', $timeOut->DateTime))->format('%H:%I:%S') : null,
            ];
        };

        usort($merged, function ($a, $b) use ($filter, $sort) {
            $dateA = Carbon::createFromFormat('Y-m-d', $a[$filter]);
            $dateB = Carbon::createFromFormat('Y-m-d', $b[$filter]);
            
            if($sort == 'asc')
                return $dateA <=> $dateB;
            else if($sort == 'desc')
                return $dateB <=> $dateA;
        });
        
        

        $page = request()->get('page', 1);

        // Calculate the offset for the items on the current page
        $offset = ($page - 1) * 10;

        // Get only the items for the current page
        $currentPageItems = array_slice($merged, $offset, 10);

        // Create a LengthAwarePaginator instance
        $mergedPaginated = new LengthAwarePaginator(
            $currentPageItems,
            count($merged),
            10,
            $page
        );

        return response()->json([
            'status' => true,
            'message' => 'Data Attendance',
            'data' => $mergedPaginated,
            'sort' => $sort,    
        ], 200);
    }
}
