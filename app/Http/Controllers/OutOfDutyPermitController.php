<?php

namespace App\Http\Controllers;

use Dompdf\Dompdf;
use HeadlessChromium\BrowserFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Spatie\Browsershot\Browsershot;
use Spatie\LaravelPdf\Facades\Pdf;
use Telegram\Bot\Laravel\Facades\Telegram;

class OutOfDutyPermitController extends Controller
{
    public function index (Request $request) {
        $filter = $request->input('filter', 'created_at');
        $sort = $request->input('sort', 'desc');
        $search = $request->input('search');

        $data = DB::table('out_of_duty AS od')
        ->select('od.*', 'si.name AS Nama', 'u.NamaUnit AS Unit', 'us.role')
        ->join('staff AS si', 'od.id_staff', '=', 'si.id')
        ->join('hr_unit AS u', 'si.id_unit', '=', 'u.IdUnit')
        ->leftJoin(DB::raw('(SELECT us.id_staff, us.role FROM users AS us WHERE us.role = 5) as us'), function ($join) {
            $join->on('si.id', '=', 'us.id_staff');
        })
        ->where(function ($query) {
            $query->where(function ($query) {
                $query->where('track', 1)
                    ->whereNotNull('us.role');
            })
                ->orWhere(function ($query) {
                    $query->whereIn('track', [2, 3, 4, 5, 6])
                        ->where('od.status', 1);
                });
        })
        ->where(function ($query) use ($search) {
            $query->where('destination', 'LIKE', '%'.$search.'%')
                ->orWhere('start_date', 'LIKE', '%'.$search.'%')
                ->orWhere('od.created_at', 'LIKE', '%'.$search.'%');
        })
        ->orderBy($filter, $sort)
        ->paginate(10);

        if($data->isEmpty()){
            return response()->json([
                'status' => false,
                'message' => 'Data not found',
                'data' => null
            ], 200);
        }

        return response()->json([
            'status' => true,
            'message' => 'Data Out of Duty',
            'data' => $data
        ], 200);
    }
    
    public function indexAll() {
        $data = DB::table('out_of_duty AS od')
        ->select('*')
        ->where('track', 6)
        ->where('status', 1)
        ->get();

        if($data->isEmpty()){
            return response()->json([
                'status' => false,
                'message' => 'Data not found',
                'data' => null
            ], 200);
        }

        return response()->json([
            'status' => true,
            'message' => 'Data Out of Duty',
            'data' => $data
        ], 200);
    }

    public function indexAllCalendar() {

        $data = DB::table('out_of_duty AS od')
        ->select('*', 'si.Nama')
        ->join('hr_staff_info AS si', 'od.id_staff', '=', 'si.FID')
        ->where('track', 6)
        ->where('status', 1)
        ->get();

        if($data->isEmpty()){
            return response()->json([
                'status' => false,
                'message' => 'Data not found',
                'data' => null
            ], 200);
        }

        return response()->json([
            'status' => true,
            'message' => 'Data Out of Duty',
            'data' => $data
        ], 200);
    }

    public function departmentIndex(Request $request) {

        $data = DB::table('out_of_duty AS od')
        ->select('od.*', 'si.name AS Nama')
        ->join('staff AS si', 'od.id_staff', '=', 'si.id')
        ->where('si.id_unit', '=', Auth::user()->id_unit)
        ->where('track', 6)
        ->where('od.status', 1)
        ->get();

        if($data->isEmpty()){
            return response()->json([
                'status' => false,
                'message' => 'Data not found',
                'data' => null
            ], 200);
        }

        return response()->json([
            'status' => true,
            'message' => 'Data Out of Duty',
            'data' => $data
        ], 200);
    }
    
    public function indexByGm(Request $request) {

        $data = DB::table('out_of_duty AS od')
        ->join('staff AS si', 'od.id_staff', '=', 'si.id')
        ->join(DB::raw('(SELECT id_staff, id, role, deleted_at FROM users WHERE role = 4 AND deleted_at IS NULL) AS u'), function ($join) {
            $join->on('u.id_staff', '=', 'si.id');
        })
        ->select('od.*', 'si.name as Nama')
        ->where('od.status', 1)
        ->where('od.track', 6)
        ->get();

        if($data->isEmpty()){
            return response()->json([
                'status' => false,
                'message' => 'Data not found',
                'data' => null
            ], 200);
        }

        return response()->json([
            'status' => true,
            'message' => 'Data Out of Duty',
            'data' => $data
        ], 200);
    }
    
    public function indexbyEmployeeApproved(Request $request) {

        $data = DB::table('out_of_duty AS od')
        ->select('od.*', 'si.Nama AS Nama')
        ->join('hr_staff_info AS si', 'od.id_staff', '=', 'si.FID')
        ->where('si.FID', '=', session('id_staff'))
        ->where('track', 6)
        ->where('status', 1)
        ->get();

        if($data->isEmpty()){
            return response()->json([
                'status' => false,
                'message' => 'Data not found',
                'data' => null
            ], 200);
        }

        return response()->json([
            'status' => true,
            'message' => 'Data Out of Duty',
            'data' => $data
        ], 200);
    }

    public function employeeIndex (Request $request) {
        $filter = $request->input('filter', 'created_at');
        $sort = $request->input('sort', 'desc');
        $search = $request->input('search');

        $data = DB::table('out_of_duty')
        ->where('id_staff', '=', session('id_staff'))
        ->where(function ($query) use ($search) {
            $query->where('destination', 'LIKE', '%'.$search.'%')
                ->orWhere('start_date', 'LIKE', '%'.$search.'%')
                ->orWhere('created_at', 'LIKE', '%'.$search.'%');
        })
        ->orderBy($filter, $sort)
        ->paginate(10);

        if($data->isEmpty()){
            return response()->json([
                'status' => false,
                'message' => 'Data not found',
                'data' => null
            ], 200);
        }

        return response()->json([
            'status' => true,
            'message' => 'Data Out of Duty',
            'data' => $data
        ], 200);
    }

    public function userIndex(Request $request) {
        $filter = $request->input('filter', 'created_at');
        $sort = $request->input('sort', 'desc');
        $search = $request->input('search');

        if($filter == 'created_at'){
            $filter = 'od.created_at';
        }
        
        $data = DB::table('out_of_duty AS od')
        ->select('od.*', 'si.name', 'u.role')
        ->join('staff AS si', 'od.id_staff', '=', 'si.id')
        ->leftJoin(DB::raw('(SELECT id_staff, id, role, deleted_at
                             FROM users
                             WHERE deleted_at IS NULL) AS u'), 'u.id_staff', '=', 'si.id')
        ->where('si.id_unit', '=', Auth::user()->id_unit)
        ->where(function ($query) {
            $query->where('od.status', '=', 1)
                ->orWhere('od.status', '=', 0);
        }) 
        ->where(function ($query) {
            $query->where('u.role', '!=', 4)
                  ->orWhereNull('u.role');
        })
        ->where(function ($query) use ($search) {
            $query->where('destination', 'LIKE', '%'.$search.'%')
                ->orWhere('start_date', 'LIKE', '%'.$search.'%')
                ->orWhere('od.created_at', 'LIKE', '%'.$search.'%');
        })
        ->orderBy($filter, $sort)
        ->paginate(10);
        if($data->isEmpty()){
            return response()->json([
                'status' => false,
                'message' => 'Data not found',
                'data' => null
            ], 200);
        }

        return response()->json([
            'status' => true,
            'message' => 'Data Out of Duty',
            'data' => $data
        ], 200);
    }

    public function gmIndex(Request $request) {
        $filter = $request->input('filter', 'created_at');
        $sort = $request->input('sort', 'desc');
        $search = $request->input('search');

        $data = DB::table('out_of_duty AS od')
        ->select('od.*', 'si.Nama AS Nama', 'u.role AS role')
        ->join('hr_staff_info AS si', 'od.id_staff', '=', 'si.FID')
        ->join('users AS u', 'si.FID', '=', 'u.id_staff')
        ->where(function($query) {
            $query->where(function($query) {
                $query->where('status', 0)
                    ->orWhere('status', 1);
            })
            ->orWhere(function($query) {
                $query->where('track', 1)
                    ->orWhere('track', 5)
                    ->orWhere('track', 6);
            });
        })
        ->where('u.role', 4)    
        ->where('u.deleted_at', null)
        ->where(function ($query) use ($search) {
            $query->where('destination', 'LIKE', '%'.$search.'%')
                ->orWhere('start_date', 'LIKE', '%'.$search.'%')
                ->orWhere('od.created_at', 'LIKE', '%'.$search.'%');
        })
        ->orderBy($filter, $sort)
        ->paginate(10);
        if($data->isEmpty()){
            return response()->json([
                'status' => false,
                'message' => 'Data not found',
                'data' => null
            ], 200);
        }

        return response()->json([
            'status' => true,
            'message' => 'Data Out of Duty',
            'data' => $data
        ], 200);
    }

    public function store (Request $request) {
        $input = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'destination' => 'required',
            'purpose' => 'required',
        ]);
        
        $id = session('id_staff');

        $staff = DB::table('hr_staff_info')
            ->where('FID', $id)
            ->first();

        $input['id_staff'] = $id;
        
        $input['status'] = 1;   
        $input['track'] = 1;
        $input['created_at'] = now();
        $input['updated_at'] = now();

        $users = DB::table('users')
        ->join('hr_staff_info', 'users.id_staff', '=', 'hr_staff_info.FID')
        ->join('telegram_session', 'telegram_session.id_staff', '=', 'hr_staff_info.FID')
        ->select('telegram_session.id')
        ->where('users.id_unit', $staff->DEPT_NAME)
        ->where('telegram_session.status', 'Active')
        ->where(function($query) {
            $query->where('role', 2)
                ->orWhere('role', 3)
                ->orWhere('role', 4);
        })
        ->where('users.deleted_at', null)
        ->get();

        if($users){
            $message = "<pre>";
            $message .= "Out of Duty request from :";
            $message .= "\nName        : " . $staff->Nama;
            $message .= "\nDestination : " . $input['destination'];
            $message .= "\nStart Date  : " . date('l, d F Y, H:i', strtotime($input['start_date']));
            $message .= "\nEnd Date    : " . date('l, d F Y, H:i', strtotime($input['end_date']));
            $message .= "\nPurpose     : " . $input['purpose'];
            $message .= "</pre>";

            foreach($users as $user){
                Telegram::sendMessage([
                    'chat_id' => $user->id,
                    'text' => $message,
                    'parse_mode' => 'HTML'
                ]);
            }
        }

        $outOfDuty = DB::table('out_of_duty')->insert($input);

        $message = "Dear bapak/ibu, saya ingin mengajukan izin keluar kantor dengan detail sebagai berikut :";
        $message .= "%0aName        : " . $staff->Nama;
        $message .= "%0aDestination : " . $input['destination'];
        $message .= "%0aStart Date  : " . date('l, d F Y, H:i', strtotime($input['start_date']));
        $message .= "%0aEnd Date    : " . date('l, d F Y, H:i', strtotime($input['end_date']));
        $message .= "%0aPurpose     : " . $input['purpose'];
        $message .= "%0aMohon dibantu untuk approval dari sistem. Terima kasih";

        return response()->json([
            'status' => 'Out of Duty request has been sent',
            'data' => $input,
            'phone_number' => $staff->Notelp,
            'message' => $message
        ], 200);
    }

    public function show ($id) {
        $data = DB::table('out_of_duty')
        ->leftJoin(DB::raw('(SELECT users.id_staff, users.deleted_at, users.role FROM users WHERE users.deleted_at IS NULL) AS us'), function ($join) {
            $join->on('out_of_duty.id_staff', '=', 'us.id_staff');
        })
        ->join('staff as si', 'si.id', '=', 'out_of_duty.id_staff')
        ->select('out_of_duty.*','us.*', 'si.name')
        ->where('out_of_duty.id', $id)
        ->first();

        $step = DB::table('out_of_duty as od')
            ->select(DB::raw("
                CASE
                    WHEN odu.track = 1 AND odu.status = 1 THEN 'Approved by Admin'
                    WHEN odu.track = 2 AND odu.status = 1 THEN 'Approved by Chief'
                    WHEN odu.track = 3 AND odu.status = 1 THEN 'Approved Asst. HOD'
                    WHEN odu.track = 4 AND odu.status = 1 THEN 'Approved by HOD'
                    WHEN odu.track = 5 AND odu.status = 1 THEN 'Approved by GM'
                    WHEN odu.track = 6 AND odu.status = 1 THEN 'Acknowledge by HRD'
                    WHEN odu.track = 0 AND odu.status = 0 THEN 'Canceled by Employee'
                    WHEN odu.track = 1 AND odu.status = 0 THEN 'Rejected by Admin'
                    WHEN odu.track = 2 AND odu.status = 0 THEN 'Rejected by Chief'
                    WHEN odu.track = 3 AND odu.status = 0 THEN 'Rejected Asst. HOD'
                    WHEN odu.track = 4 AND odu.status = 0 THEN 'Rejected by HOD'
                    WHEN odu.track = 5 AND odu.status = 0 THEN 'Rejected by GM'
                    WHEN odu.track = 6 AND odu.status = 0 THEN 'Rejected by HRD'
                END as approval_status"), 'u.role', 'odu.track', 'odu.status', 'si.name', 'odu.created_at', 'odu.note')
            ->join('out_of_duty_update as odu', 'od.id', '=', 'odu.id_out_of_duty')
            ->leftJoin('users as u', 'u.id', '=', 'odu.id_user')
            ->leftJoin('staff AS si', 'si.id', '=', 'u.id_staff')
            ->where('od.id', $id)
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Data Out of Duty',
            'data' => $data,
            'step' => $step
        ], 200);
    }

    public function employeeCancel (Request $request, $id) {
        $input = $request->validate([
            'note' => 'required'
        ]);

        $data = DB::table('out_of_duty')
        ->where('id_staff', session('id_staff'))
        ->where('id', $id)
        ->where('status', 1)
        ->update(['status' => 0]);

        DB::table('out_of_duty_update')
            ->insert([
                'id_out_of_duty' => $id,
                'id_user' => null,
                'track' => 0,
                'status' => 0,
                'created_at' => now(),
                'note' => $input['note'],   
            ]);

        if($data){
            return response()->json([
                'status' => true,
                'message' => 'Out of Duty request has been canceled',
                'data' => null
            ], 200);
        }

        return response()->json([
            'status' => false,
            'message' => 'Out of Duty request on process',
            'data' => null
        ], 200);
    }

    public function approve ($id) {
        if(Auth::user()->role == 2){
            $track = 2;
        }else if(Auth::user()->role == 3){
            $track = 3;
        }else if(Auth::user()->role == 4){
            $track = 4;
        }else if(Auth::user()->role == 5){
            $track = 5;
        }else if(Auth::user()->role == 6){
            $track = 6;
        }
        $user = DB::table('out_of_duty_update')
            ->insert([
                'id_out_of_duty' => $id,
                'id_user' => Auth::user()->id,
                'track' => $track,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ]);

        $data = DB::table('out_of_duty')
        ->where('id', $id)
        ->update(['track' => $track]);

        if($data){
            return response()->json([
                'status' => true,
                'message' => 'Out of Duty request has been approved',
                'data' => null
            ], 200);
        }

        return response()->json([
            'status' => false,
            'message' => 'Out of Duty request failed to approve',
            'data' => null
        ], 200);
    }

    public function reject (Request $request, $id) {
        $input = $request->validate([
            'note' => 'required'
        ]);

        if(Auth::user()->role == 2){
            $track = 2;
        }else if(Auth::user()->role == 3){
            $track = 3;
        }else if(Auth::user()->role == 4){
            $track = 4;
        }else if(Auth::user()->role == 5){
            $track = 5;
        }
        $data = DB::table('out_of_duty')
        ->where('id', $id)
        ->update(['status' => 0, 'track' => $track]);

        DB::table('out_of_duty_update')
            ->insert([
                'id_out_of_duty' => $id,
                'id_user' => Auth::user()->id,
                'track' => $track,
                'status' => 0,
                'created_at' => now(),
                'note' => $input['note'],   
            ]);

        if($data){
            return response()->json([
                'status' => true,
                'message' => 'Out of Duty request has been rejected',
                'data' => null
            ], 200);
        }

        return response()->json([
            'status' => false,
            'message' => 'Out of Duty request failed to reject',
            'data' => null
        ], 200);
    }

    public function downloadView(Request $request) {
        $id = $request->input('id');
        $ids = $request->input('ids');

        $data = DB::table('out_of_duty AS od')
        ->join('hr_staff_info AS si', 'od.id_staff', '=', 'si.FID')
        ->select('si.Nama AS name', 'od.*')
        ->where('id', $id)
        ->where('status', 1)
        ->where('track', '>=', 1)
        ->where('od.id_staff', $ids)
        ->first();

        $user = DB::table('out_of_duty_update AS ou')
        ->join('users AS u', 'ou.id_user', '=', 'u.id')
        ->join('hr_staff_info AS si', 'u.id_staff', '=', 'si.FID')
        ->select('si.Nama AS name', 'u.role', 'ou.*')
        ->where('ou.id_out_of_duty', $id)
        ->get();

        $data->date = date('l, d F Y', strtotime($data->start_date));
        $data->start_time = date('H:m', strtotime($data->start_date));
        $data->end_time = date('H:m', strtotime($data->end_date));
        $data->name = mb_convert_case($data->name, MB_CASE_TITLE, 'UTF-8');
        for($i = 0; $i < count($user); $i++){
            $user[$i]->name = mb_convert_case($user[$i]->name, MB_CASE_TITLE, 'UTF-8');
        }
        return view('outofduty', ['data' => $data, 'users' => $user]);
    }

    public function download (Request $request) {
        $id = $request->input('id');
        $ids = $request->input('ids');

        $data = DB::table('out_of_duty AS od')
        ->join('hr_staff_info AS si', 'od.id_staff', '=', 'si.FID')
        ->select('si.Nama AS name', 'od.*')
        ->where('id', $id)
        ->where('status', 1)
        ->where('track', '>=', 1)
        ->where('od.id_staff', $ids)
        ->first();

        $user = DB::table('out_of_duty_update AS ou')
        ->join('users AS u', 'ou.id_user', '=', 'u.id')
        ->join('hr_staff_info AS si', 'u.id_staff', '=', 'si.FID')
        ->select('si.Nama AS name', 'u.role', 'ou.*')
        ->where('ou.id_out_of_duty', $id)
        ->get();

        $data->date = date('l, d F Y', strtotime($data->start_date));
        $data->start_time = date('H:m', strtotime($data->start_date));
        $data->end_time = date('H:m', strtotime($data->end_date));
        $data->name = mb_convert_case($data->name, MB_CASE_TITLE, 'UTF-8');
        for($i = 0; $i < count($user); $i++){
            $user[$i]->name = mb_convert_case($user[$i]->name, MB_CASE_TITLE, 'UTF-8');
        }

        while(count($user) < 2){
            $newData = (object) [
                'name' => ' ',
                'role' => ' ',
                'id_out_of_duty' => ' ',
                'id_user' => ' ',
                'track' => ' ',
                'status' => ' ',
                'created_at' => ' ',
                'note' => ' ',
                
            ];

            $user->push($newData);
        }


        $dompdf = new Dompdf();
        $dompdf->loadHtml(view('outofduty', ['data' => $data, 'users' => $user, 'id' => $id, 'ids' => $ids])->render());
        $dompdf->render();
        // return(view('outofduty', ['data' => $data, 'users' => $user])->render());
        return $dompdf->stream('out_of_duty-'.now()->format('Y-m-d').'.pdf');
        // Output the PDF to the browser for debugging purposes
        return response()->streamDownload(function () use ($dompdf) {
            echo $dompdf->output();
        }, "out_of_duty-{$id}.pdf");

        // $dompdf = new Dompdf();

        // $dompdf->setPaper('A4', 'landscape');
        // $dompdf->loadHtml(view('outofduty', ['data' => $data, 'users' => $user])->render());
        // return $dompdf->stream(view('outofduty', ['data' => $data, 'users' => $user])->render());

        // return Pdf::view('outofduty', ['data' => $data, 'users' => $user])
        //     ->format('a4')
        //     ->name('out_of_duty-'.now()->format('Y-m-d').'.pdf')
        //     ->withBrowsershot(function (Browsershot $browsershot) {
        //         $browsershot->scale(1)
        //             ->setChromePath('C:\Users\henry\.cache\puppeteer\chrome\win64-123.0.6312.122\chrome-win64\chrome.exe');
        //     });;

        

        // $browser = (new BrowserFactory("C:\Users\henry\cache\puppeteer\chrome-headless-shell\win64-123.0.6312.122\chrome-headless-shell-win64\chrome-headless-shell.exe"))->createBrowser([
        //         'windowSize' => [1920, 1080],
        //     ]);

        // try {

        //     /* creates a new page and navigate to an URL */
        //     $page = $browser->createPage();
        //     $page->navigate(env('LINK')."/outofduty/view?id=".$id."&ids=".$ids)->waitForNavigation();
        //     $pageTitle = $page->evaluate('document.title')->getReturnValue();

        //     $options = [
        //         'landscape'           => true,
        //         'printBackground'     => false,
        //         'marginTop'           => 0.0, 
        //         'marginBottom'        => 0.0, 
        //         'marginLeft'          => 0.0,
        //         'marginRight'         => 0.0, 
        //         'headerTemplate'      => '<div class="grid justify-center">
        //         <div class="w-fit">
        //         {!! QrCode::size(128)->merge("/public/storage/Logo.png")
        //             ->generate("http://192.168.77.209/royal_backend/public/outofduty/download?id={$data->id}&ids={$data->id_staff}") 
        //         !!}
        //         </div>
        //     </div>',
        //     ];

        //     $name = public_path("uploads/".time().'.pdf');
        //     $page->pdf($options)->saveToFile($name);

        //     return response()->download($name);

        // } finally {

        //     $browser->close();

        // }
    }
}
