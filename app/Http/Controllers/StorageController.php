<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class StorageController extends Controller
{
    public function downloadStaffTemplate(Request $request)
    {
        return response()->download(public_path('storage\staff_import_template.csv'), 'staff_import_template.csv');
        $filePath = public_path('storage\staff_import_template.csv');

        // if (!Storage::exists($filePath)) {
        //     abort(404);
        // }

        // return response()->download($filePath, 'staff_import_template.xlsx');
    }

    public function downloadAnnualLeaveTemplate(Request $request)
    {
        return response()->download(public_path('storage\annual_leave_import_template.csv'), 'annual_leave_import_template.csv');
    }

    public function downloadExtraOffTemplate(Request $request)
    {
        return response()->download(public_path('storage\extra_off_import_template.csv'), 'extra_off_import_template.csv');
    }

    
}
