<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Auth;

class StaffController extends Controller
{
    public function indexAll (Request $request){

        $result = DB::table('hr_staff_info AS si')
        ->join('hr_unit AS u', 'si.DEPT_NAME', '=', 'u.IdUnit')
        ->select('si.FID AS FID', 'si.Nama AS Nama', 'si.DEPT_NAME', 'u.Namaunit', 'si.JABATAN')
        ->orderBy('si.FID', 'asc')
        ->get();

        return response()->json([
            'status' => true,
            'message' => 'Data Staff',
            'data' => $result
        ], 200);
    }

    public function index (Request $request){
        $filter = $request->input('filter', 'FID');
        $sort = $request->input('sort', 'asc');
        $search = $request->input('search');

        $result = DB::table('hr_staff_info AS si')
        ->join('hr_unit AS u', 'si.DEPT_NAME', '=', 'u.IdUnit')
        ->select('si.FID AS FID', 'si.Nama AS Nama', 'si.NIK', 'u.Namaunit', 'si.JABATAN', 'si.TGL_MASUK', 'si.Notelp')
        ->where('si.Nama', 'like', '%'.$search.'%')
        ->orWhere('si.FID', 'like', '%'.$search.'%')
        ->orWhere('u.Namaunit', 'like', '%'.$search.'%')
        ->orWhere('si.JABATAN', 'like', '%'.$search.'%')
        ->orderBy($filter, $sort)
            ->paginate(10);

        return response()->json([
            'status' => true,
            'message' => 'Data Staff',
            'data' => $result
        ], 200);
    }

    public function indexbyDepartment () {
        $user = DB::table('users')
            ->join('hr_staff_info', 'users.id_staff', '=', 'hr_staff_info.FID')
            ->where('id_staff', '=', Auth::user()->id_staff)
            ->first();

        $data = DB::table('hr_staff_info AS si')
            ->select('si.FID AS FID', 'si.Nama AS Nama', 'si.JABATAN')
            ->orderBy('si.Nama', 'asc')
            ->where('si.DEPT_NAME', '=', $user->DEPT_NAME)
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Data Staff',
            'data' => $data
        ], 200);
    }

    public function show (Request $request){
        $id = $request->input('id');

        $result = DB::table('hr_staff_info AS si')
        ->join('hr_unit AS u', 'si.DEPT_NAME', '=', 'u.IdUnit')
        ->select('si.FID AS FID', 'si.Nama AS Nama', 'si.NIK', 'u.Namaunit', 'si.DEPT_NAME', 'si.JABATAN', 'si.TGL_MASUK', 'si.Notelp')
        ->where('si.FID', '=', $id)
        ->first();

        return response()->json([
            'status' => true,
            'message' => 'Data Staff',
            'data' => $result
        ], 200);
    }

    public function store (Request $request){
        $input = $request->validate([
            'FID' => 'required',
            'Nama' => 'required',
            'NIK' => 'required',
            'DEPT_NAME' => 'required',
            'JABATAN' => 'required',
            'TGL_MASUK' => 'required',
            'Notelp' => 'required'
        ]);

        $result = DB::table('hr_staff_info')
        ->insert($input);
        
        if($result){
            return response()->json([
                'status' => true,
                'message' => 'Data Staff berhasil ditambahkan',
                'data' => $input
            ], 200);
        }
        
        return response()->json([
            'status' => false,
            'message' => 'Data Staff gagal ditambahkan',
            'data' => null
        ], 422);
    }

    public function update (Request $request){
        $input = $request->validate([
            'FID' => 'required',
            'Nama' => 'required',
            'NIK' => 'required',
            'DEPT_NAME' => 'required',
            'JABATAN' => 'required',
            'TGL_MASUK' => 'required',
            'Notelp' => 'required'
        ]);
        $result = DB::table('hr_staff_info')    
        ->where('FID', $input['FID'])
        ->update($input);
        
        return response()->json([
            'status' => true,
            'message' => 'Data Staff berhasil diupdate',
            'data' => $result
        ], 200);
    }

    public function destroy (Request $request){
        $id = $request->input('id');

        $result = DB::table('hr_staff_info')
        ->where('FID', $id)
        ->delete();
        
        if($result){
            return response()->json([
                'status' => true,
                'message' => 'Data Staff berhasil dihapus',
                'data' => $id
            ], 200);
        }
        
        return response()->json([
            'status' => false,
            'message' => 'Data Staff gagal dihapus',
            'data' => null
        ], 422);
    }
}
