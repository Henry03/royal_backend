<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DepartmentController extends Controller
{
    public function indexUnit (){   
        $result = DB::table('hr_unit')
        ->select('IdUnit AS idUnit', 'Namaunit AS unit', 'JABATAN AS jabatan')
        ->join('hr_staff_info', 'hr_unit.IdUnit', '=', 'hr_staff_info.DEPT_NAME')
        ->whereNotNull('Namaunit')
        ->where('Namaunit', '!=', '')
        ->whereNotNull('JABATAN')
        ->where('JABATAN', '!=', '')
        ->orderBy('Namaunit', 'asc')
        ->orderBy('jabatan', 'asc')
        ->distinct()
        ->get();

        return response()->json([
            'status' => true,
            'message' => 'Data Unit',
            'data' => $result
        ], 200);
    }

    // public function indexPosition (){
    //     $result = DB::table('hr_staff_info')
    //     ->select('JABATAN')
    //     ->join
    //     ->distinct()
    //     ->where()
    //     ->orderBy('JABATAN', 'asc')
    //     ->get();

    //     return response()->json([
    //         'status' => true,
    //         'message' => 'Data Position',
    //         'data' => $result
    //     ], 200);
    // }
}
