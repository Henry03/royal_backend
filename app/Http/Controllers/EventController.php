<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EventController extends Controller
{
    public function index (){
        $data = DB::table('ta_hari_libur')
        ->select('ID', 'tgl_libur', 'Nama_libur', 'TpLIbur')
        ->get();

        foreach($data as $key => $value){
            $data[$key]->start = date('Y-m-d', strtotime(str_replace('/', '-', $value->tgl_libur)));
            $data[$key]->end = date('Y-m-d', strtotime(str_replace('/', '-', $value->tgl_libur)));
            $data[$key]->title = $value->Nama_libur;
            $data[$key]->id = $value->ID;
            unset($data[$key]->tgl_libur);
            unset($data[$key]->Nama_libur);
            unset($data[$key]->ID);
            unset($data[$key]->TpLIbur);
        }

        return response()->json([
            'status' => true,
            'message' => 'Data Event',
            'data' => $data
        ], 200);
    }
    public function indexByDate (Request $request){
        $date = $request->input('date', now()->format('Y-m-d'));
        $filter = $request->input('filter', 'tgl_libur');
        $sort = $request->input('sort', 'asc');
        $search = $request->input('search');

        if($filter == 'tgl_libur'){
            $filter = DB::raw("STR_TO_DATE(tgl_libur, '%d/%m/%Y')");
        }

        $data = DB::table('ta_hari_libur')
        ->where(DB::raw("STR_TO_DATE(tgl_libur, '%d/%m/%Y')"), '>=', $date)
        ->where(function ($query)  use ($search) {
            $query->where('Nama_libur', 'like', '%' .$search. '%')
                  ->orWhere('tgl_libur', 'like', '%' . $search . '%');
        })
        ->orderBy($filter, $sort)
        ->paginate(10);

        foreach($data as $key => $value){
            $data[$key]->start = date('Y-m-d', strtotime(str_replace('/', '-', $value->tgl_libur)));
            $data[$key]->end = date('Y-m-d', strtotime(str_replace('/', '-', $value->tgl_libur)));
            $data[$key]->title = $value->Nama_libur;
            $data[$key]->id = $value->ID;
            unset($data[$key]->tgl_libur);
            unset($data[$key]->Nama_libur);
            unset($data[$key]->ID);
            unset($data[$key]->TpLIbur);
        }

        return response()->json([
            'status' => true,
            'message' => 'Data Event',
            'data' => $data
        ], 200);
    }

    public function store (Request $request){
        $input = $request->validate([
            'tgl_libur' => 'required',
            'Nama_libur' => 'required',
        ]);

        $input['tgl_libur'] = date('d/m/Y', strtotime($input['tgl_libur']));

        $result = DB::table('ta_hari_libur')
        ->insert($input);
        
        if($result){
            return response()->json([
                'status' => true,
                'message' => 'Data Event berhasil ditambahkan',
                'data' => $input
            ], 200);
        }
    }

    public function show ($id){
        $data = DB::table('ta_hari_libur')
        ->where('ID', $id)
        ->first();

        return response()->json([
            'status' => true,
            'message' => 'Data Event',
            'data' => $data
        ], 200);
    }

    public function update (Request $request, $id){
        $input = $request->validate([
            'tgl_libur' => 'required',
            'Nama_libur' => 'required',
        ]);

        $result = DB::table('ta_hari_libur')
        ->where('ID', $id)
        ->update([
            'tgl_libur' => $input['tgl_libur'],
            'Nama_libur' => $input['Nama_libur']
        ]);
        
        if ($result !== false) {
            if ($result > 0) {
                // Data was updated
                return response()->json([
                    'status' => true,
                    'message' => 'Data Event berhasil diubah',
                    'data' => $input
                ], 200);
            } else {
                // No changes were made, but request is still successful
                return response()->json([
                    'status' => true,
                    'message' => 'Data Event tidak berubah',
                    'data' => $input
                ], 200);
            }
        }

        return response()->json([
            'status' => false,
            'message' => 'Data Event gagal diubah',
            'data' => null
        ], 422);
    }

    public function destroy (Request $request){
        $data = $request->validate([
            'id' => 'required'
        ]);

        $result = DB::table('ta_hari_libur')
        ->where('ID', $data['id'])
        ->delete();
        
        if($result){
            return response()->json([
                'status' => true,
                'message' => 'Data Event berhasil dihapus',
                'data' => $result
            ], 200);
        }

        return response()->json([
            'status' => false,
            'message' => 'Data Event gagal dihapus',
            'data' => $result
        ], 422);
    }
}
