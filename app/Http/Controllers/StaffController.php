<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
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
        ->orderBy('si.Nama', 'asc')
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
        $unit = $request->input('unit', '%%');

        $result = DB::table('hr_staff_info AS si')
        ->join('hr_unit AS u', 'si.DEPT_NAME', '=', 'u.IdUnit')
        ->select('si.FID AS FID', 'si.Nama AS Nama', 'si.NIK', 'u.Namaunit', 'si.JABATAN', 'si.TGL_MASUK', 'si.Notelp')
        ->where(function ($query) use ($search) {
            $query->where('si.Nama', 'like', '%'.$search.'%')
            ->orWhere('si.FID', 'like', '%'.$search.'%')
            ->orWhere('u.Namaunit', 'like', '%'.$search.'%')
            ->orWhere('si.JABATAN', 'like', '%'.$search.'%');
        })
        ->where('si.DEPT_NAME', 'like', '%'.$unit.'%')
        ->orderBy($filter, $sort)
        ->paginate(20);
        // ->toSql();

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
    
    public function staffProfile (Request $request){
        $id = session('id_staff');

        $result = DB::table('hr_staff_info AS si')
        ->join('hr_unit AS u', 'si.DEPT_NAME', '=', 'u.IdUnit')
        ->select('si.FID AS id', 'si.Nama AS name', 'si.nik', 'u.Namaunit as unit', 'si.DEPT_NAME as id_unit', 'si.JABATAN as position', 'si.TGL_MASUK as entry_date', 'si.Notelp as phone_number')
        ->where('si.FID', '=', $id)
        ->first();

        $result->entry_date = date('Y-m-d', strtotime($result->entry_date));

        return response()->json([
            'status' => true,
            'message' => 'Data Staff',
            'data' => $result
        ], 200);
    }

    public function userProfile (Request $request){
        $id = Auth::user()->id_staff;

        $result = DB::table('hr_staff_info AS si')
        ->join('hr_unit AS u', 'si.DEPT_NAME', '=', 'u.IdUnit')
        ->join('users AS us', 'si.FID', '=', 'us.id_staff')
        ->select('si.FID AS id', 'si.Nama AS name', 'si.NIK as nik', 'u.Namaunit as unit', 'si.DEPT_NAME as id_unit', 'si.JABATAN as position', 'si.TGL_MASUK as entry_date', 'si.Notelp as phone_number', 'us.username')
        ->where('si.FID', '=', $id)
        ->first();

        $result->entry_date = date('Y-m-d', strtotime($result->entry_date));

        return response()->json([
            'status' => true,
            'message' => 'Data Staff',
            'data' => $result
        ], 200);
    }

    public function store (Request $request){
        $input = $request->validate([
            'Nama' => 'required',
            'NIK' => 'required',
            'DEPT_NAME' => 'required',
            'JABATAN' => 'required',
            'TGL_MASUK' => 'required',
            'Notelp' => 'required'
        ]);

        $input['TGL_MASUK'] = Carbon::createFromFormat('Y-m-d', $input['TGL_MASUK'])->format('d/m/Y');

        $result = DB::table('hr_staff_info')
        ->insert($input);

        DB::table('staff')
        ->insert([
            'id' => DB::getPdo()->lastInsertId(),
            'id_unit' => $input['DEPT_NAME'],
            'name' => $input['Nama'],
            'nik' => $input['NIK'],
            'position' => $input['JABATAN'],
            'entry_date' => $request->input('TGL_MASUK'),
            'phone_number' => $input['Notelp'],
            'status' => 'Active'
        ]);
        
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

        $input['TGL_MASUK'] = Carbon::createFromFormat('Y-m-d', $input['TGL_MASUK'])->format('d/m/Y');
        $result = DB::table('hr_staff_info')    
        ->where('FID', $input['FID'])
        ->update($input);

        DB::table('staff')
        ->where('id', $input['FID'])
        ->update([
            'id_unit' => $input['DEPT_NAME'],
            'name' => $input['Nama'],
            'nik' => $input['NIK'],
            'position' => $input['JABATAN'],
            'entry_date' => $request->input('TGL_MASUK'),
            'phone_number' => $input['Notelp'],
        ]);
        
        return response()->json([
            'status' => true,
            'message' => 'Data Staff berhasil diupdate',
            'data' => $result
        ], 200);
    }

    public function updateProfile (Request $request){ 
        $input = $request->validate([
            'phone_number' => 'required'
        ]);
        $id = session('id_staff');

        $result = DB::table('hr_staff_info')
        ->where('FID', $id)
        ->update(['Notelp' => $input['phone_number']]);

        DB::table('staff')
        ->where('id', $id)
        ->update(['phone_number' => $input['phone_number']]);

        return response()->json([
            'status' => true,
            'message' => 'Data Staff berhasil diupdate',
            'data' => $result
        ], 200);
    }

    public function updateProfileUser (Request $request){ 
        $input = $request->validate([
            'Notelp' => 'required',
            'username' => 'required',
        ]);
        $id = Auth::user()->id_staff;

        if($request->input('password') != ""){
            $input['password'] = bcrypt($request->input('password'));
        }
        $result = DB::table('hr_staff_info')
        ->join('users', 'hr_staff_info.FID', '=', 'users.id_staff')
        ->where('FID', $id)
        ->update($input);

        DB::table('staff')
        ->join('users', 'staff.id', '=', 'users.id_staff')
        ->where('id', $id)
        ->update([
            'phone_number' => $input['Notelp'],
            'username' => $input['username'],
            'password' => $input['password']
        ]);


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

        DB::table('staff')
        ->where('id', $id)
        ->update(['status' => 'Inactive']);
        
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

    public function uploadCsv(Request $request)
    {
        $file = $request->file('csv');

        $data = array_map('str_getcsv', file($file));
        $items = [];
        $headerSkipped = false;

        foreach ($data as $row) {
            if(!$headerSkipped) {
                $headerSkipped = true;
                continue;
            }
            $rowData = explode(";", $row[0]);

            if(strcasecmp($rowData[2], 'FRONT OFFICE') == 0 || strcasecmp($rowData[2], 'a000000001') == 0){
                $unit = 'a000000001';
            }else if(strcasecmp($rowData[2], 'HOUSEKEEPING') == 0 || strcasecmp($rowData[2], 'a000000002') == 0){
                $unit = 'a000000002';
            }else if(strcasecmp($rowData[2], 'F&B SERVICE') == 0 || strcasecmp($rowData[2], 'a000000003') == 0){
                $unit = 'a000000003';
            }else if(strcasecmp($rowData[2], 'F&B PRODUCT') == 0 || strcasecmp($rowData[2], 'a000000004') == 0){
                $unit = 'a000000004';
            }else if(strcasecmp($rowData[2], 'SALES & MARKETING') == 0 || strcasecmp($rowData[2], 'a000000005') == 0){
                $unit = 'a000000005';
            }else if(strcasecmp($rowData[2], 'ACCOUNTING') == 0 || strcasecmp($rowData[2], 'a000000006') == 0){
                $unit = 'a000000006';
            }else if(strcasecmp($rowData[2], 'ENGINEERING') == 0 || strcasecmp($rowData[2], 'a000000007') == 0){
                $unit = 'a000000007';
            }else if(strcasecmp($rowData[2], 'HRD') == 0 || strcasecmp($rowData[2], 'a000000008') == 0){
                $unit = 'a000000008';
            }else if(strcasecmp($rowData[2], 'AMBARRUKMO LAUNDRY') == 0 || strcasecmp($rowData[2], 'a000000009') == 0){
                $unit = 'a000000009';
            }else if(strcasecmp($rowData[2], 'OS SECURITY') == 0 || strcasecmp($rowData[2], 'a000000010') == 0){
                $unit = 'a000000010';
            }else if(strcasecmp($rowData[2], 'OS GARDENER') == 0 || strcasecmp($rowData[2], 'a000000011') == 0){
                $unit = 'a000000011';
            }else if(strcasecmp($rowData[2], 'RESIGN') == 0 || strcasecmp($rowData[2], 'a000000012') == 0){
                $unit = 'a000000012';
            }else if(strcasecmp($rowData[2], 'EXECUTIVE OFFICE') == 0 || strcasecmp($rowData[2], 'a000000013') == 0){
                $unit = 'a000000013';
            }else if(strcasecmp($rowData[2], 'ROYAL AMBARRUKMO') == 0 || strcasecmp($rowData[2], 'a000000014') == 0){
                $unit = 'a000000014';
            }else if(strcasecmp($rowData[2], 'OS STEWARD') == 0 || strcasecmp($rowData[2], 'a000000015') == 0){
                $unit = 'a000000015';
            }else if(strcasecmp($rowData[2], 'ROOM') == 0 || strcasecmp($rowData[2], 'a000000016') == 0){
                $unit = 'a000000016';
            }else if(strcasecmp($rowData[2], 'DANNYCO') == 0 || strcasecmp($rowData[2], 'a000000017') == 0){
                $unit = 'a000000017';
            }else if(strcasecmp($rowData[2], 'TRAINEE') == 0 || strcasecmp($rowData[2], 'a000000018') == 0){
                $unit = 'a000000018';
            }else if(strcasecmp($rowData[2], 'FO') == 0 || strcasecmp($rowData[2], 'a000000019') == 0){
                $unit = 'a000000019';
            }else if(strcasecmp($rowData[2], 'HK') == 0 || strcasecmp($rowData[2], 'a000000020') == 0){
                $unit = 'a000000020';
            }else if(strcasecmp($rowData[2], 'FBS') == 0 || strcasecmp($rowData[2], 'a000000021') == 0){
                $unit = 'a000000021';
            }else if(strcasecmp($rowData[2], 'FBP') == 0 || strcasecmp($rowData[2], 'a000000022') == 0){
                $unit = 'a000000022';
            }else if(strcasecmp($rowData[2], 'HR') == 0 || strcasecmp($rowData[2], 'a000000023') == 0){
                $unit = 'a000000023';
            }else if(strcasecmp($rowData[2], 'ACCT') == 0 || strcasecmp($rowData[2], 'a000000024') == 0){
                $unit = 'a000000024';
            }else if(strcasecmp($rowData[2], 'ENG') == 0 || strcasecmp($rowData[2], 'a000000025') == 0){
                $unit = 'a000000025';
            }else {
                $unit = null;
            }

            $department = DB::table('hr_unit')
                ->where('IdUnit', $unit)
                ->first();

            $items[] = [
                'name' => $rowData[0],
                'nik' => $rowData[1],
                'id_unit' => $unit,
                'unit_name' => $department->Namaunit,
                'position' => $rowData[3],
                'entry_date' => Carbon::createFromFormat('d/m/Y', str_replace('\/', '/', $rowData[4]))->format('d/m/Y'),
                'phone_number' => $rowData[5],
            ];

            // DB::table('hr_staff_info')->insert([
            //     'Nama' => $rowData[0],
            //     'NIK' => $rowData[1],
            //     'DEPT_NAME' => $unit,
            //     'JABATAN' => $rowData[3],
            //     'TGL_MASUK' => Carbon::createFromFormat('d/m/Y', str_replace('\/', '/', $rowData[4]))->format('d/m/Y'),
            //     'Notelp' => $rowData[5],
            // ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Data Staff berhasil diimport',
            'data' => $items,
        ], 200);
    }

    public function importArray (Request $request){
        $input = $request->input('data');

        foreach($input as $row){
            DB::table('hr_staff_info')->insert([
                'Nama' => $row['name'],
                'NIK' => $row['nik'],
                'DEPT_NAME' => $row['id_unit'],
                'JABATAN' => $row['position'],
                'TGL_MASUK' => Carbon::createFromFormat('d/m/Y', str_replace('\/', '/', $row['entry_date']))->format('Y-m-d'),
                'Notelp' => $row['phone_number'],
            ]);

            DB::table('staff')->insert([
                'id' => DB::getPdo()->lastInsertId(),
                'id_unit' => $row['id_unit'],
                'name' => $row['name'],
                'nik' => $row['nik'],
                'position' => $row['position'],
                'entry_date' => $row['entry_date'],
                'phone_number' => $row['phone_number'],
                'status' => 'Active'
            ]);
        }
        return response()->json([
            'status' => true,
            'message' => 'Data Staff berhasil diimport',
            'data' => $input
        ], 200);
    }

    public function countNewStaff(Request $request) {
        $input = $request->validate([
            'year' => 'required'
        ]);
        
        $data = DB::table('staff as si')
        ->selectRaw("si.entry_date AS day")
        ->selectRaw("COUNT(*) as value")
        ->whereYear('si.entry_date', $input['year'])
        ->groupBy('si.entry_date')
        ->get();

        return response()->json([
            'status' => true,
            'message' => 'Data Staff',
            'data' => $data
        ], 200);
    }
}
