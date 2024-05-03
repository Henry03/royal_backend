<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function index (Request $request){
        $filter = $request->input('filter', 'FID');
        $sort = $request->input('sort', 'asc');
        $search = $request->input('search');

        $result = DB::table('hr_staff_info AS si')
        ->join('users AS u', 'si.FID', '=', 'u.id_staff')
        ->select('u.id', 'si.FID AS FID', 'si.Nama AS Nama', 'si.NIK', 'u.username', 'u.role', 'si.JABATAN', 'si.TGL_MASUK', 'si.Notelp')
        ->where('u.deleted_at', '=', null)
        ->where(function ($query) use ($search, $filter, $sort) {
            $query->where('si.Nama', 'like', '%'.$search.'%')
                ->orWhere('si.FID', 'like', '%'.$search.'%')
                ->orWhere('u.username', 'like', '%'.$search.'%')
                ->orWhere('u.role', 'like', '%'.$search.'%')
                ->orWhere('si.JABATAN', 'like', '%'.$search.'%')
                ->orderBy($filter, $sort);
        })
            ->paginate(10);

        return response()->json([
            'status' => true,
            'message' => 'Data User',
            'data' => $result
        ], 200);
    }

    public function show ($id){

        $result = DB::table('hr_staff_info AS si')
        ->join('users AS u', 'si.FID', '=', 'u.id_staff')
        ->join('hr_unit AS hu', 'si.DEPT_NAME', '=', 'hu.IdUnit')
        ->select('si.FID AS FID', 'si.Nama AS Nama', 'si.NIK', 'u.username', 'u.role', 'si.JABATAN', 'si.TGL_MASUK', 'si.Notelp', 'hu.NamaUnit', 'si.DEPT_NAME as id_unit')
        ->where('u.id', '=', $id)
        ->first();

        return response()->json([
            'status' => true,
            'message' => 'Data User',
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

    public function update (Request $request, $id){
        $input = $request->validate([
            'id_staff' => 'required',
            'username' => 'required',
            'role' => 'required',
            'id_unit' => 'required',
        ]);
        $result = User::where('id', $id)
        ->first();


        if ($result) {
            if ($request->input('password') != "") {
                $input['password'] = bcrypt($request->input('password'));
            }
        
            $result->fill($input);
            $result->save();

            return response()->json([
                'status' => true,
                'message' => 'Data Staff berhasil diupdate',
                'data' => $result
            ], 200);    
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Data Staff gagal diupdate',
                'data' => null
            ], 422);
        }
    }

    public function destroy (Request $request){
        $id = $request->input('id');

        if($id == 1){
            return response()->json([
                'status' => false,
                'message' => 'Cant delete superadmin user',
                'data' => null
            ], 422);
        }

        $result = User::where('id', $id)
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
