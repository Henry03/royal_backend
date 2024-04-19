<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use Telegram\Bot\Laravel\Facades\Telegram;
use App\Exports\FormExport;

class FormController extends Controller
{
    public function index() {
        $data = DB::table('form as f')
            ->select('f.id', 'f.title')
            ->selectRaw('SUM(CASE WHEN sd.status = 0 THEN 1 ELSE 0 END) AS waiting')
            ->selectRaw('SUM(CASE WHEN sd.status = 1 THEN 1 ELSE 0 END) AS form_filled')
            ->selectRaw('f.participant')
            ->selectRaw('ROUND((SUM(CASE WHEN sd.status = 1 THEN 1 ELSE 0 END) / f.participant) * 100, 2) AS percentage')
            ->leftJoin('staff_data as sd', 'f.id', '=', 'sd.id_form')
            ->leftJoin('staff as si', 'si.id', '=', 'sd.id_staff')
            ->where(function ($query) {
                $query->where('si.status', 'Active')
                    ->orWhereNull('si.status');
            })
            ->where('si.position', '!=', 'TRAINEE')
            ->orWhereNull('si.position')
            ->groupBy('f.id', 'f.title', 'f.participant')
            ->orderBy('f.created_at', 'desc')
            ->paginate(10);

        if($data){
            return response()->json([
                'status' => true,
                'message' => 'Get form data successfully.',
                'data' => $data
            ], 200);
        }

        return response()->json([
            'status' => false,
            'message' => 'Failed to get form data.',
            'data' => null
        ], 400);
    }

    public function responseIndex (Request $request) {
        $filter = $request->input('filter', 'name');
        $sort = $request->input('sort', 'asc');
        $search = $request->input('search', '%%');
        $unit = $request->input('id_unit', '%%');
        $id = $request->input('id');

        $data = DB::table('staff_data as sd')
            ->select('sd.status', 'sd.created_at', 'si.name', 'hu.Namaunit', 'sd.id')
            ->join('staff as si', 'si.id', '=', 'sd.id_staff')
            ->join('hr_unit as hu', 'hu.IdUnit', '=', 'si.id_unit')
            ->where('sd.id_form', '=', $id)
            ->where('si.id_unit', 'like', $unit)
            ->where('si.name', 'like', '%' . $search . '%')
            ->orderBy($filter, $sort)
            ->paginate(20);

        $form = DB::table('form')
            ->where('id', $id)
            ->first();

        if($data){
            return response()->json([
                'status' => true,
                'message' => 'Get form response data successfully.',
                'data' => $data,
                'form' => $form
            ], 200);
        }

        return response()->json([
            'status' => false,
            'message' => 'Failed to get form response data.',
            'data' => null
        ], 400);

    }

    public function individualResponse(Request $request){
        $idForm = $request->input('idForm');

        $form = DB::table('staff_data as sd')
            ->selectRaw('SUM(CASE WHEN sd.status = 1 THEN 1 ELSE 0 END) AS checked')
            ->selectRaw('COUNT(*) AS total')
            ->where('sd.id_form', '=', $idForm)
            ->first();

        if($form->total == 0){
            return response()->json([
                'status' => false,
                'message' => 'Form data not found.',
                'code' => '-1',
                'data' => null
            ], 400);
        }

        $data = DB::table('staff_data as sd')
                ->select('sd.*', 'si.name', 'hu.Namaunit')
                ->join('staff as si', 'si.id', '=', 'sd.id_staff')
                ->join('hr_unit as hu', 'hu.IdUnit', '=', 'si.id_unit')
                ->where('sd.id_form', '=', $idForm)
                ->where('sd.status', '=', 0)
                ->orderBy('sd.created_at', 'asc')
                ->paginate(1);

        if($data->isEmpty()){
            return response()->json([
                'status' => false,
                'message' => 'Form data not found.',
                'code' => '-2',
                'data' => $form
            ], 400);
        }
        
        $child = DB::table('child_data')
            ->where('id_staff_data', '=', $data->items()[0]->id)
            ->get();

        $emergency = DB::table('emergency_contact_data')
            ->where('id_staff_data', '=', $data->items()[0]->id)
            ->get();

        $data->items()[0]->child = $child;
        $data->items()[0]->emergency = $emergency;

        return response()->json([
            'status' => true,
            'message' => 'Get form response data successfully.',
            'data' => $data,
            'form' => $form
        ], 200);
    }
    
    public function individualResponseById(Request $request){
        $id = $request->input('id', 0);
        $idForm = $request->input('idForm');

        $form = DB::table('staff_data as sd')
            ->selectRaw('SUM(CASE WHEN sd.status = 1 THEN 1 ELSE 0 END) AS checked')
            ->selectRaw('COUNT(*) AS total')
            ->where('sd.id_form', '=', $idForm)
            ->first();

        if($form->total == 0){
            return response()->json([
                'status' => false,
                'message' => 'Form data not found.',
                'code' => '-1',
                'data' => null
            ], 400);
        }

        $data = DB::table('staff_data as sd')
                ->select('sd.*', 'si.name', 'hu.Namaunit')
                ->join('staff as si', 'si.id', '=', 'sd.id_staff')
                ->join('hr_unit as hu', 'hu.IdUnit', '=', 'si.id_unit')
                ->where('sd.id_form', '=', $idForm)
                ->where('sd.id', '=', $id)
                ->orderBy('sd.created_at', 'asc')
                ->first();
        
        $child = DB::table('child_data')
            ->where('id_staff_data', '=', $data->id)
            ->get();

        $emergency = DB::table('emergency_contact_data')
            ->where('id_staff_data', '=', $data->id)
            ->get();

        $data->child = $child;
        $data->emergency = $emergency;

        return response()->json([
            'status' => true,
            'message' => 'Get form response data successfully.',
            'data' => $data,
            'form' => $form
        ], 200);
    }

    public function show($id) {
        $data = DB::table('form')
            ->leftJoin('staff_data', function($join) {
                $join->on('form.id', '=', 'staff_data.id_form')
                    ->where('staff_data.id_staff', '=', session('id_staff'));
            })
            ->where('form.id', '=', $id)
            ->first();
            
        if(!$data){
            return response()->json([
                'status' => false,
                'message' => 'Form data not found.',
                'code' => '-1',
                'data' => null
            ], 400);
        }
        
        $staff = DB::table('staff')
            ->join('hr_unit', 'hr_unit.IdUnit', '=', 'staff.id_unit')
            ->select('staff.name', 'hr_unit.Namaunit as department', 'staff.position')
            ->where('id', session('id_staff'))
            ->first();

        if($staff->position == 'TRAINEE'){
            return response()->json([
                'status' => false,
                'message' => 'You are not allowed to fill this form.',
                'code' => '-2',
                'data' => null
            ], 400);
        }

        $child = DB::table('child_data')
            ->where('id_staff_data', '=', $data->id)
            ->get();

        $emergency = DB::table('emergency_contact_data')
            ->where('id_staff_data', '=', $data->id)
            ->get();

        if ($emergency->isEmpty()) {
            $emergency = [
                [
                    'name' => '',
                    'phone_number' => '',
                    'relationship' => ''
                ]
            ];
        }

        if($data){
            return response()->json([
                'status' => true,
                'message' => 'Get form data successfully.',
                'data' => $data,
                'child' => $child,
                'emergency' => $emergency,
                'staff' => $staff
            ], 200);
        }

        return response()->json([
            'status' => false,
            'message' => 'Failed to get form data.',
            'data' => null
        ], 400);
    }

    public function userShow($id) {
        $data = DB::table('form')
            ->leftJoin('staff_data', function($join) {
                $join->on('form.id', '=', 'staff_data.id_form')
                    ->where('staff_data.id_staff', '=', Auth::user()->id_staff);
            })
            ->leftJoin('staff', 'staff.id', '=', 'staff_data.id_staff')
            ->leftJoin('hr_unit', 'hr_unit.IdUnit', '=', 'staff.id_unit')
            ->select('form.*', 'staff_data.*', 'staff.name', 'staff.id_unit', 'hr_unit.Namaunit as department')
            ->where('form.id', '=', $id)
            ->first();
            
        if(!$data){
            return response()->json([
                'status' => false,
                'message' => 'Form data not found.',
                'code' => '-1',
                'data' => null
            ], 400);
        }
        $staff = DB::table('staff')
            ->join('hr_unit', 'hr_unit.IdUnit', '=', 'staff.id_unit')
            ->select('staff.name', 'hr_unit.Namaunit as department')
            ->where('id', Auth::user()->id_staff)
            ->first();

        $child = DB::table('child_data')
            ->where('id_staff_data', '=', $data->id)
            ->get();

        $emergency = DB::table('emergency_contact_data')
            ->where('id_staff_data', '=', $data->id)
            ->get();

        if ($emergency->isEmpty()) {
            $emergency = [
                [
                    'name' => '',
                    'phone_number' => '',
                    'relationship' => ''
                ]
            ];
        }

        if($data){
            return response()->json([
                'status' => true,
                'message' => 'Get form data successfully.',
                'data' => $data,
                'staff' => $staff,
                'child' => $child,
                'emergency' => $emergency
            ], 200);
        }

        return response()->json([
            'status' => false,
            'message' => 'Failed to get form data.',
            'data' => null
        ], 400);
    }

    public function isStaffFilled(){
        $staffId = session('id_staff');
        $data = DB::table('form AS f')
        ->leftJoin('staff_data AS sd', function ($join) use ($staffId) {
            $join->on('f.id', '=', 'sd.id_form')
                 ->where('sd.id_staff', '=', $staffId);
        })
        ->where('f.start_datetime', '<=', now())
        ->where('f.end_datetime', '>=', now())
        ->where(function ($query) {
            $query->whereNull('sd.id')
                  ->orWhereNull('sd.status');
        })
        ->whereRaw('NOT EXISTS (SELECT position FROM staff WHERE id = ? AND position LIKE "%trainee%")', [$staffId])
        ->select('f.id', 'f.title')
        ->get();
    
        
        if($data){
            return response()->json([
                'status' => true,
                'message' => 'There are forms that need to be filled by staff.',
                'data' => $data
            ], 200);
        }

        return response()->json([
            'status' => false,
            'message' => 'There are no forms that need to be filled by staff.',
            'data' => null
        ], 200);
    }

    public function isUserStaffFilled(){
        $staffId = Auth::user()->id_staff;
        $data = DB::table('form AS f')
        ->leftJoin('staff_data AS sd', function ($join) use ($staffId) {
            $join->on('f.id', '=', 'sd.id_form')
                 ->where('sd.id_staff', '=', $staffId);
        })
        ->where('f.start_datetime', '<=', now())
        ->where('f.end_datetime', '>=', now())
        ->where(function ($query) {
            $query->whereNull('sd.id')
                  ->orWhereNull('sd.status');
        })
        ->whereRaw('NOT EXISTS (SELECT position FROM staff WHERE id = ? AND position LIKE "%trainee%")', [$staffId])
        ->select('f.id', 'f.title')
        ->get();
        
        if($data){
            return response()->json([
                'status' => true,
                'message' => 'There are forms that need to be filled by staff.',
                'data' => $data
            ], 200);
        }

        return response()->json([
            'status' => false,
            'message' => 'There are no forms that need to be filled by staff.',
            'data' => null
        ], 200);
    }

    public function store (Request $request) {
        $input = $request->validate([
            'title' => 'required',
            'description' => 'required',
            'start_datetime' => 'required',
            'end_datetime' => 'required',
        ]);

        $participantCount = DB::table('staff')
            ->select(DB::raw('COUNT(*) AS participant'))
            ->where(function ($query) {
                $query->where('status', 'Active')
                    ->orWhereNull('status');
            })
            ->where('position', '!=', 'TRAINEE')
            ->count();

        $input['participant'] = $participantCount;

        $data = DB::table('form')
            ->insert($input);

        $users = DB::table('telegram_session as ts')
        ->join('staff as s', 'ts.id_staff', '=', 's.id')
        ->where('s.position', '!=', 'trainee')
        ->select('ts.id')
        ->get();


            $start_date = Carbon::parse($input['start_datetime']);
            $end_date = Carbon::parse($input['end_datetime']);
            if($users){
                $message = "📢 Attention all staff members:";
                $message .= "\n\nTo ensure our records are accurate, it's important that you update your information in the <b>".$input['title']."</b> form. Here's how:";
                $message .= "\n1. Login to the HRIS System.";
                $message .= "\n2. Click on your profile.";
                $message .= "\n3. Navigate to the <b>".$input['title']."</b>.";
                $message .= "\n4. Fill out the form accordingly.";
                $message .= "\n\nPlease complete this update between ".$start_date->toFormattedDateString()." and ".$end_date->toFormattedDateString();
                $message .= "\n\nYour cooperation is crucial in maintaining accurate records. Let's ensure our information is up-to-date!";
                $message .= "\n\nThank you for your prompt attention to this matter.";
    
                foreach($users as $user){
                    Telegram::sendMessage([
                        'chat_id' => $user->id,
                        'text' => $message,
                        'parse_mode' => 'HTML'
                    ]);
                }
            }

        if($data){
            return response()->json([
                'status' => true,
                'message' => 'Form data has been successfully added.',
                'data' => $input
            ], 200);
        }

        return response()->json([
            'status' => false,
            'message' => 'Failed to add form data.',
            'data' => null
        ], 400);

    }

    public function staffDataStore (Request $request) {
        $input = $request->validate([
            'email' => 'required|email',
            'id_form' => 'required',
            'birth_date' => 'required|date',
            'nik' => 'required|numeric|digits:16',
            'npwp' => 'required|regex:/^[0][1-9][.]([\d]{3})[.]([\d]{3})[.][\d][-]([\d]{3})[.]([\d]{3})$/',
            'ktp_address' => 'required',
            'address' => 'required',
            'blood_type' => 'required',
            'phone_number' => 'required'
        ]);

        $input['id_staff'] = session('id_staff');

        if($request->id){
            $data = DB::table('staff_data')
                ->where('id', $request->id)
                ->update($input);

            $id = $request->id;
        }else{
            $data = DB::table('staff_data')
                ->insert($input);

            $id = DB::getPdo()->lastInsertId();
        }

        return response()->json([
            'status' => true,
            'message' => 'Staff data has been successfully added.',
            'data' => $data,
            'id' => $id
        ], 200);
    }

    public function staffFamilyStore (Request $request) {
        $input = $request->validate([
            'id' => 'required',
            'mother_name' => 'required'
        ]);

        if($request->spouse_name || $request->spouse_birth_date){
            $spouse = $request->validate([
                'spouse_name' => 'required|string|max:100',
                'spouse_birth_date' => 'required|date',
            ], [
                'spouse_name.required' => 'The spouse name field is required.',
                'spouse_name.string' => 'The spouse name must be a string.',
                'spouse_name.max' => 'The spouse name may not be greater than :max characters.',
                'spouse_birth_date.required' => 'The spouse birthdate field is required.',
                'spouse_birth_date.date' => 'The spouse birthdate must be a valid date.',
            ]);

            $data = DB::table('staff_data')
                ->where('id', $input['id'])
                ->update([
                    'mother_name' => $request->mother_name,
                    'spouse_name' => $request->spouse_name,
                    'spouse_birth_date' => $request->spouse_birth_date
                ]);
        }

        if($request->child){
            $child = $request->validate([
                'child.*' => 'required',
                'child.*.name' => 'required|string|max:100', 
                'child.*.birth_date' => 'required|date',
            ], [
                'child.array' => 'Child information must be an array.',
                'child.*.name.required' => 'The name field is required for each child.',
                'child.*.name.string' => 'The name must be a string.',
                'child.*.name.max' => 'The name may not be greater than :max characters.',
                'child.*.birth_date.required' => 'The birthdate field is required for each child.',
                'child.*.birth_date.date' => 'The birthdate must be a valid date.',
            ]);

            DB::table('child_data')
                ->where('id_staff_data', $request->id)
                ->delete();

            foreach($request->child as $child) {
                DB::table('child_data')->insert([
                    'id_staff_data' => $request->id,
                    'name' => $child['name'],
                    'birth_date' => $child['birth_date']
                ]);
            }
        }

        $data = DB::table('staff_data')
            ->where('id', $input['id'])
            ->update($input);

        return response()->json([
            'status' => true,
            'message' => 'Staff family data has been successfully updated.',
            'data' => $data
        ], 200);
    }

    public function staffEmergencyStore (Request $request) {
        $input = $request->validate([
            'id' => 'required',
            'contact' => 'required|array',
            'contact.*.name' => 'required|string|max:100',
            'contact.*.phone_number' => 'required|numeric',
            'contact.*.relationship' => 'required'
        ],[
            'contact.required' => 'Emergency contact information is required.',
            'contact.array' => 'Emergency contact information must be an array.',
            'contact.*.name.required' => 'The name field is required',
            'contact.*.name.string' => 'The name must be a string.',
            'contact.*.name.max' => 'The name may not be greater than :max characters.',
            'contact.*.phone_number.required' => 'The phone number field is required',
            'contact.*.phone_number.numeric' => 'The phone number must be a number.',
            'contact.*.relationship.required' => 'The relationship field is required'
        ]);

        DB::table('emergency_contact_data')
                ->where('id_staff_data', $request->id)
                ->delete();

        foreach($request->contact as $contact) {
            $data = DB::table('emergency_contact_data')->insert([
                'id_staff_data' => $request->id,
                'name' => $contact['name'],
                'relationship' => $contact['relationship'],
                'phone_number' => $contact['phone_number']
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Staff emergency contact data has been successfully updated.',
            'data' => $data
        ], 200);
    }

    public function staffDocumentStore (Request $request) {
        $input = $request->validate([
            'id' => 'required',
            'ktp_image' => 'required|image|mimes:jpeg,png,jpg|max:1024',
            'kk_image' => 'required|image|mimes:jpeg,png,jpg|max:1024',
            'npwp_image' => 'required|image|mimes:jpeg,png,jpg|max:1024',
            'sim_a_image' => 'image|mimes:jpeg,png,jpg|max:1024',
            'bpjs_tk_image' => 'required|image|mimes:jpeg,png,jpg|max:1024',
            'bpjs_kesehatan_image' => 'required|image|mimes:jpeg,png,jpg|max:1024',
            'status' => 'required|numeric'
        ]);

        $data = $request->validate([
            'title' => 'required',
            'id' => 'required',
            'id_staff' => 'required',
            'id_form' => 'required',
        ]);

        $uploadFolder = str_replace(' ', '_', $request->title);

        if($input['status'] == 1){
            Storage::disk('disk')->put($uploadFolder . $data['id_staff'].'/'.'KTP'.'_'.$data['id_form'].'_'.date('Y-m-d_H-i-s').'.'.$request->file('ktp_image')->getClientOriginalExtension(), $request->file('ktp_image'));
            // $input['ktp_image'] = $request->file('ktp_image')->storeAs($uploadFolder, $data['id_staff'].'/'.'KTP'.'_'.$data['id_form'].'_'.date('Y-m-d_H-i-s').'.'.$request->file('ktp_image')->getClientOriginalExtension(), 'public');
            // $input['kk_image'] = $request->file('kk_image')->storeAs($uploadFolder, $data['id_staff'].'/'.'KK'.'_'.$data['id_form'].'_'.date('Y-m-d_H-i-s').'.'.$request->file('kk_image')->getClientOriginalExtension(), 'public');
            // $input['npwp_image'] = $request->file('npwp_image')->storeAs($uploadFolder, $data['id_staff'].'/'.'NPWP'.'_'.$data['id_form'].'_'.date('Y-m-d_H-i-s').'.'.$request->file('npwp_image')->getClientOriginalExtension(), 'public');
            // $input['bpjs_tk_image'] = $request->file('bpjs_tk_image')->storeAs($uploadFolder, $data['id_staff'].'/'.'BPJS_TK'.'_'.$data['id_form'].'_'.date('Y-m-d_H-i-s').'.'.$request->file('bpjs_tk_image')->getClientOriginalExtension(), 'public');
            // $input['bpjs_kesehatan_image'] = $request->file('bpjs_kesehatan_image')->storeAs($uploadFolder, $data['id_staff'].'/'.'BPJS_KESEHATAN'.'_'.$data['id_form'].'_'.date('Y-m-d_H-i-s').'.'.$request->file('bpjs_kesehatan_image')->getClientOriginalExtension(), 'public');

            if($input['sim_a_image']){
                $input['sim_a_image'] = $request->file('sim_a_image')->storeAs($uploadFolder, $data['id_staff'].'/'.'SIM_A'.'_'.$data['id_form'].'_'.date('Y-m-d_H-i-s').'.'.$request->file('sim_a_image')->getClientOriginalExtension(), 'public');
            }

            $data = DB::table('staff_data')
                ->where('id', $input['id'])
                ->update($input);

            return response()->json([
                'status' => true,
                'message' => 'Staff document data has been successfully updated.',
                'data' => $data
            ], 200);
        }else if ($input['status'] != 1) {
            throw ValidationException::withMessages(['status' => 'Please check the box to proceed.']);
        }
        

        return response()->json([
            'status' => false,
            'message' => 'Failed to update staff document data.',
            'data' => null
        ], 400);
    }

    public function userStaffDataStore (Request $request) {
        $input = $request->validate([
            'email' => 'required|email',
            'id_form' => 'required',
            'birth_date' => 'required|date',
            'nik' => 'required|numeric|digits:16',
            'npwp' => 'required|regex:/^[0][1-9][.]([\d]{3})[.]([\d]{3})[.][\d][-]([\d]{3})[.]([\d]{3})$/',
            'ktp_address' => 'required',
            'address' => 'required',
            'blood_type' => 'required',
            'phone_number' => 'required'
        ]);

        $input['id_staff'] = Auth::user()->id_staff;

        if($request->id){
            $data = DB::table('staff_data')
                ->where('id', $request->id)
                ->update($input);

            $id = $request->id;
        }else{
            $data = DB::table('staff_data')
                ->insert($input);

            $id = DB::getPdo()->lastInsertId();
        }

        return response()->json([
            'status' => true,
            'message' => 'Staff data has been successfully added.',
            'data' => $data,
            'id' => $id
        ], 200);
    }

    public function userStaffFamilyStore (Request $request) {
        $input = $request->validate([
            'id' => 'required',
            'mother_name' => 'required'
        ]);

        if($request->spouse_name || $request->spouse_birth_date){
            $spouse = $request->validate([
                'spouse_name' => 'required|string|max:100',
                'spouse_birth_date' => 'required|date',
            ], [
                'spouse_name.required' => 'The spouse name field is required.',
                'spouse_name.string' => 'The spouse name must be a string.',
                'spouse_name.max' => 'The spouse name may not be greater than :max characters.',
                'spouse_birth_date.required' => 'The spouse birthdate field is required.',
                'spouse_birth_date.date' => 'The spouse birthdate must be a valid date.',
            ]);

            $data = DB::table('staff_data')
                ->where('id', $input['id'])
                ->update([
                    'mother_name' => $request->mother_name,
                    'spouse_name' => $request->spouse_name,
                    'spouse_birth_date' => $request->spouse_birth_date
                ]);
        }

        if($request->child){
            $child = $request->validate([
                'child.*' => 'required',
                'child.*.name' => 'required|string|max:100', 
                'child.*.birth_date' => 'required|date',
            ], [
                'child.array' => 'Child information must be an array.',
                'child.*.name.required' => 'The name field is required for each child.',
                'child.*.name.string' => 'The name must be a string.',
                'child.*.name.max' => 'The name may not be greater than :max characters.',
                'child.*.birth_date.required' => 'The birthdate field is required for each child.',
                'child.*.birth_date.date' => 'The birthdate must be a valid date.',
            ]);

            DB::table('child_data')
                ->where('id_staff_data', $request->id)
                ->delete();

            foreach($request->child as $child) {
                DB::table('child_data')->insert([
                    'id_staff_data' => $request->id,
                    'name' => $child['name'],
                    'birth_date' => $child['birth_date']
                ]);
            }
        }

        $data = DB::table('staff_data')
            ->where('id', $input['id'])
            ->update($input);

        return response()->json([
            'status' => true,
            'message' => 'Staff family data has been successfully updated.',
            'data' => $data
        ], 200);
    }

    public function userStaffEmergencyStore (Request $request) {
        $input = $request->validate([
            'id' => 'required',
            'contact' => 'required|array',
            'contact.*.name' => 'required|string|max:100',
            'contact.*.phone_number' => 'required|numeric',
            'contact.*.relationship' => 'required'
        ],[
            'contact.required' => 'Emergency contact information is required.',
            'contact.array' => 'Emergency contact information must be an array.',
            'contact.*.name.required' => 'The name field is required',
            'contact.*.name.string' => 'The name must be a string.',
            'contact.*.name.max' => 'The name may not be greater than :max characters.',
            'contact.*.phone_number.required' => 'The phone number field is required',
            'contact.*.phone_number.numeric' => 'The phone number must be a number.',
            'contact.*.relationship.required' => 'The relationship field is required'
        ]);

        DB::table('emergency_contact_data')
                ->where('id_staff_data', $request->id)
                ->delete();

        foreach($request->contact as $contact) {
            $data = DB::table('emergency_contact_data')->insert([
                'id_staff_data' => $request->id,
                'name' => $contact['name'],
                'relationship' => $contact['relationship'],
                'phone_number' => $contact['phone_number']
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Staff emergency contact data has been successfully updated.',
            'data' => $data
        ], 200);
    }

    public function userStaffDocumentStore (Request $request) {
        $input = $request->validate([
            'id' => 'required',
            'ktp_image' => 'required|image|mimes:jpeg,png,jpg|max:1024',
            'kk_image' => 'required|image|mimes:jpeg,png,jpg|max:1024',
            'npwp_image' => 'required|image|mimes:jpeg,png,jpg|max:1024',
            'sim_a_image' => 'image|mimes:jpeg,png,jpg|max:1024',
            'bpjs_tk_image' => 'required|image|mimes:jpeg,png,jpg|max:1024',
            'bpjs_kesehatan_image' => 'required|image|mimes:jpeg,png,jpg|max:1024',
            'marriage_certificate_image' => 'image|mimes:jpeg,png,jpg|max:1024',
            'divorce_certificate_image' => 'image|mimes:jpeg,png,jpg|max:1024',
            'status' => 'required'
        ],[
            'status.required' => 'Please check the box to proceed.'
        ]);

        $data = $request->validate([
            'title' => 'required',
            'id' => 'required',
            'id_staff' => 'required',
            'id_form' => 'required',
        ]);

        $uploadFolder = str_replace(' ', '_', $request->title) . '/' . $data['id_staff'];

        if($input['status'] == 0){
            $input['ktp_image'] = Storage::putFileAs($uploadFolder, $request->file('ktp_image'), 'KTP'.'_'.$data['id_form'].'_'.date('Y-m-d_H-i-s').'.'.$request->file('ktp_image')->getClientOriginalExtension());
            $input['kk_image'] = Storage::putFileAs($uploadFolder, $request->file('kk_image'), 'KK'.'_'.$data['id_form'].'_'.date('Y-m-d_H-i-s').'.'.$request->file('kk_image')->getClientOriginalExtension());
            $input['npwp_image'] = Storage::putFileAs($uploadFolder, $request->file('npwp_image'), 'NPWP'.'_'.$data['id_form'].'_'.date('Y-m-d_H-i-s').'.'.$request->file('npwp_image')->getClientOriginalExtension());
            $input['bpjs_tk_image'] = Storage::putFileAs($uploadFolder, $request->file('bpjs_tk_image'), 'BPJS_TK'.'_'.$data['id_form'].'_'.date('Y-m-d_H-i-s').'.'.$request->file('bpjs_tk_image')->getClientOriginalExtension());
            $input['bpjs_kesehatan_image'] = Storage::putFileAs($uploadFolder, $request->file('bpjs_kesehatan_image'), 'BPJS_KESEHATAN'.'_'.$data['id_form'].'_'.date('Y-m-d_H-i-s').'.'.$request->file('bpjs_kesehatan_image')->getClientOriginalExtension());

            if(isset($input['sim_a_image'])){
                $input['sim_a_image'] = Storage::putFileAs($uploadFolder, $request->file('sim_a_image'), 'SIM_A'.'_'.$data['id_form'].'_'.date('Y-m-d_H-i-s').'.'.$request->file('sim_a_image')->getClientOriginalExtension());
            }
            if(isset($input['marriage_certificate_image'])){
                $input['marriage_certificate_image'] = Storage::putFileAs($uploadFolder, $request->file('marriage_certificate_image'), 'MARRIAGE_CERTIFICATE'.'_'.$data['id_form'].'_'.date('Y-m-d_H-i-s').'.'.$request->file('marriage_certificate_image')->getClientOriginalExtension());
            }
            if(isset($input['divorce_certificate_image'])){
                $input['divorce_certificate_image'] = Storage::putFileAs($uploadFolder, $request->file('divorce_certificate_image'), 'DIVORCE_CERTIFICATE'.'_'.$data['id_form'].'_'.date('Y-m-d_H-i-s').'.'.$request->file('divorce_certificate_image')->getClientOriginalExtension());
            }

            $data = DB::table('staff_data')
                ->where('id', $input['id'])
                ->update($input);

            return response()->json([
                'status' => true,
                'message' => 'Staff document data has been successfully updated.',
                'data' => $data
            ], 200);
        }else if ($input['status'] != 0) {
            throw ValidationException::withMessages(['status' => 'Please check the box to proceed.']);
        }
        

        return response()->json([
            'status' => false,
            'message' => 'Failed to update staff document data.',
            'data' => null
        ], 400);
    }
    
    public function staffDataAccept (Request $request) {
        $input = $request->validate([
            'id' => 'required',
            'category' => 'required',
        ],[
            'category.required' => 'Select the PTKP category.'
        
        ]);

        $input['status'] = 1;

        $data = DB::table('staff_data')
            ->where('id', $input['id'])
            ->update($input);

        return response()->json([
            'status' => true,
            'message' => 'Staff data accepted',
            'data' => $data
        ], 200);
    }

    public function destroy (Request $request){
        $input = $request->validate([
            'id' => 'required'
        ]);

        $data = DB::table('form')
            ->where('id', $input['id'])
            ->delete();

        if($data){
            return response()->json([
                'status' => true,
                'message' => 'Form data has been successfully deleted.',
                'data' => $input
            ], 200);
        }

        return response()->json([
            'status' => false,
            'message' => 'Failed to delete form data.',
            'data' => null
        ], 400);
    }

    public function staffDataDestroy (Request $request){
        $input = $request->validate([
            'id' => 'required'
        ]);

        $data = DB::table('staff_data')
            ->where('id', $input['id'])
            ->delete();

        if($data){
            return response()->json([
                'status' => true,
                'message' => 'Staff data has been successfully deleted.',
                'data' => $input
            ], 200);
        }

        return response()->json([
            'status' => false,
            'message' => 'Failed to delete staff data.',
            'data' => null
        ], 400);
    }

    public function getImage(Request $request){
        $path = storage_path('app\\'.str_replace('/storage/', '', $request->input('path')));
        if(!File::exists($path)) {
            abort(404);
        }

        $file = File::get($path);
        $type = File::mimeType($path);

        $response = Response::make($file, 200);
        $response->header("Content-Type", $type);

        return $response;
    }

    public function exportToExcel ($id) {

        return Excel::download(new FormExport($id), 'staff_data.xlsx');

    }
}
