<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ImportController extends Controller
{
    public function uploadCsvAL(Request $request)
    {
        $file = $request->file('csv');

        $data = array_map('str_getcsv', file($file));
        $items = [];
        $headerSkipped = false;
        $id = 1;

        foreach ($data as $row) {
            if(!$headerSkipped) {
                $headerSkipped = true;
                continue;
            }
            $rowData = explode(";", $row[0]);
            $user = DB::table('staff')
                ->where('id', $rowData[0])
                ->select('id', 'name')
                ->first();

            $items[] = [
                'id' => $id++,
                'id_staff' => $rowData[0],
                'name' => $user->name,
                'date' => $rowData[1],
            ];
        }

        return response()->json([
            'status' => true,
            'message' => 'Data AL berhasil diimport',
            'data' => $items,
        ], 200);
    }

    public function importArrayAL (Request $request){
        $input = $request->input('data');

        foreach($input as $row){
            DB::table('annual_leave')->insert([
                'id_staff' => $row['id_staff'],
                'date' => Carbon::createFromFormat('d/m/Y', $row['date'])->format('Y-m-d')
            ]);
        }
        return response()->json([
            'status' => true,
            'message' => 'Data Annual Leave berhasil diimport',
            'data' => $input
        ], 200);
    }

    public function uploadCsvEO(Request $request)
    {
        $file = $request->file('csv');

        $data = array_map('str_getcsv', file($file));
        $items = [];
        $headerSkipped = false;
        $id = 1;

        foreach ($data as $row) {
            if(!$headerSkipped) {
                $headerSkipped = true;
                continue;
            }
            $rowData = explode(";", $row[0]);

            $user = DB::table('staff')
                ->where('id', $rowData[0])
                ->select('id', 'name')
                ->first();

            $items[] = [
                'id' => $id++,
                'id_staff' => $rowData[0],
                'name' => $user->name,
                'date' => $rowData[1],
            ];
        }

        return response()->json([
            'status' => true,
            'message' => 'Data Extra Off berhasil diimport',
            'data' => $items,
        ], 200);
    }

    public function importArrayEO (Request $request){
        $input = $request->input('data');

        foreach($input as $row){
            DB::table('extra_off')->insert([
                'id_staff' => $row['id_staff'],
                'date' => Carbon::createFromFormat('d/m/Y', $row['date'])->format('Y-m-d'),
                'expire' => Carbon::createFromFormat('d/m/Y', $row['date'])->endOfMonth()->format('Y-m-d')
            ]);
        }
        return response()->json([
            'status' => true,
            'message' => 'Data Extra Off berhasil diimport',
            'data' => $input
        ], 200);
    }
}
