<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShiftController extends Controller
{
    public function store (Request $request) {
        $input = $request->validate([
            'id_staff' => 'required',
            'date' => 'required|date',
            'id' => 'required'
        ]);

        $result = DB::table('ta_jadwal_staffx')
            ->insert([
                'Fid' => $input['id_staff'],
                'Tanggal' => Carbon::parse($input['date'])->format('d/m/Y'),
                'NoJadwal' => $input['id']
            ]);
        
        if ($result) {
            return response()->json([
                'status' => true,
                'message' => 'Schedule set succesfully',
                'data' => $input
            ], 200);
        } else {
        return response()->json([
            'status' => false,
            'message' => 'Schedule set failed',
            'data' => $input
        ], 500);
        }
        
    }

    public function destroy (Request $request) {
        $input = $request->validate([
            'id_staff' => 'required',
            'date' => 'required'
        ]);

        $result = DB::table('ta_jadwal_staffx')
        ->where('Fid', $input['id_staff'])
        ->whereRaw("STR_TO_DATE(Tanggal, '%d/%m/%Y') = ?", [$input['date']])
        ->delete();

        if ($result) {
            return response()->json([
                'status' => true,
                'message' => 'Schedule unset successfully',
            ], 200);
        }

        return response()->json([
            'status' => false,
            'message' => 'Failed to unset schedule',
        ], 500);
    }
}
