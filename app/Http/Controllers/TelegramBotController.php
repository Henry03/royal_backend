<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Telegram\Bot\Keyboard\Keyboard;
use Telegram\Bot\Laravel\Facades\Telegram;

class TelegramBotController extends Controller
{
    public function indexStaff (Request $request){
        $filter = $request->input('filter', 'FID');
        $sort = $request->input('sort', 'asc');
        $search = $request->input('search');
        $unit = $request->input('unit', '%%');

        $result = DB::table('staff AS si')
        ->join('hr_unit AS u', 'si.id_unit', '=', 'u.IdUnit')
        ->select('si.id AS id', 'si.name', 'si.nik', 'u.Namaunit', 'si.position')
        ->where(function ($query) use ($search) {
            $query->where('si.Nama', 'like', '%'.$search.'%')
            ->orWhere('si.FID', 'like', '%'.$search.'%')
            ->orWhere('u.Namaunit', 'like', '%'.$search.'%')
            ->orWhere('si.JABATAN', 'like', '%'.$search.'%');
        })
        ->where('si.id_unit', 'like', '%'.$unit.'%')
        ->orderBy($filter, $sort)
        ->paginate(20);
        // ->toSql();

        return response()->json([
            'status' => true,
            'message' => 'Data Staff',
            'data' => $result
        ], 200);
    }

    public function show(Request $request) {
        $id = $request->input('id');
        $staff = DB::table('staff AS si')
            ->join('hr_unit AS u', 'si.id_unit', '=', 'u.IdUnit')
            ->select('si.id AS id', 'si.name', 'u.Namaunit', 'si.position')
            ->where('si.id', $id)
            ->first();
        $data = DB::table('telegram_session as ts')
            ->where('ts.id_staff', $id)
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Data Staff',
            'staff' => $staff,
            'data' => $data
        ], 200);
    }

    public function activate(Request $request) {
        $id = $request->input('id');
        $data = DB::table('telegram_session')
            ->where('id', $id)
            ->update(['status' => 'Active']);

        return response()->json([
            'status' => true,
            'message' => 'Data Staff',
            'data' => $data,
            'id' => $id
        ], 200);
    }

    public function revoke(Request $request) {
        $input = $request->validate([
            'id' => 'required'
        ]);
        $id = $request->input('id');
        $data = DB::table('telegram_session')
            ->where('id', $id)
            ->update(['status' => 'Inactive']);

        return response()->json([
            'status' => true,
            'message' => 'Data Staff',
            'data' => $data
        ], 200);
    }

    public function ban (Request $request) {
        $input = $request->validate([
            'id' => 'required'
        ]);
        $id = $request->input('id');
        $data = DB::table('telegram_session')
            ->where('id', $id)
            ->update(['status' => 'Banned']);

        return response()->json([
            'status' => true,
            'message' => 'Data Staff',
            'data' => $data
        ], 200);
    }
    
    public function setWebhook () {
        $response = Telegram::setWebhook(['url' => env('NGROK').env('TELEGRAM_WEBHOOK_URL')]);

        return response()->json([
            'status' => true,
            'message' => 'Webhook has been set',
            'data' => $response
        ], 200);
    }

    public function commandHandlerWebhook (Request $request) {
        $update = Telegram::commandsHandler(true);

        $chat_id = $update->getChat()->getId();

        $data = DB::table('telegram_session')
            ->where('id', $chat_id)
            ->first();
        
        if($data && $data->status == "Banned"){
            $message = "Maaf, akun anda telah di banned";
            $message .= "\nSilahkan hubungi HRD untuk informasi lebih lanjut.";
            Telegram::sendMessage([
                'chat_id' => $chat_id,
                'text' => $message,
            ]);
            return;
        }
        
        if (($update->getMessage()->getContact() !== null || preg_match('/^(?:\+?62|0)8[1-9][0-9]{6,9}$/', $update->getMessage()->getText()))) {
            $data = DB::table('telegram_session')
                ->where('id', $chat_id)
                ->first();

            if($data && ($data->status === 'Pending' || $data->status === 'Inactive')) {
                $phone_number = $update->getMessage()->getContact() !== null ? $update->getMessage()->getContact()->getPhoneNumber() : $update->getMessage()->getText();
                
                if (substr($phone_number, 0, 3) == '+628') {
                    $phone_number = substr_replace($phone_number, '08', 0, 3);
                } else if (substr($phone_number, 0, 3) == '628') {
                    $phone_number = substr_replace($phone_number, '08', 0, 3);
                }
                $phone_number = str_replace('+62', '0', $phone_number);
    
                // Check if the phone number matches any record in the database
                $staff_info = DB::table('hr_staff_info as si')
                    ->join('hr_unit as u', 'si.DEPT_NAME', '=', 'u.IdUnit')
                    ->where('Notelp', $phone_number)
                    ->first();
    
                // Handle the found staff info
                if ($staff_info) {
                    // Update or insert the phone number and other details into the telegram_session table
                    DB::table('telegram_session')
                        ->updateOrInsert([
                            'id' => $chat_id,
                        ], [
                            'first_name' => $update->getMessage()->getChat()->getFirstName(),
                            'username' => $update->getMessage()->getChat()->getUsername(),
                            'phone_number' => $phone_number,
                            'status' => 'Pending',
                            'id_staff' => $staff_info->FID,
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
    
                    // Construct and send the response message
                    $message = "Data ditemukan:\n";
                    $message .= "Nama : " . $staff_info->Nama . "\n";
                    $message .= "Departement : " . $staff_info->Namaunit . "\n";
                    $message .= "Jabatan : " . $staff_info->JABATAN . "\n";
                    $message .= "Apakah data ini benar?";
                    
                    Telegram::sendMessage([
                        'chat_id' => $chat_id,
                        'text' => $message,
                        'reply_markup' => json_encode([
                            'keyboard' => [[['text' => 'Ya'], ['text' => 'Tidak']]],
                            'resize_keyboard' => true,
                            'one_time_keyboard' => true
                        ])
                    ]);
                } else {
                    DB::table('telegram_session')
                    ->where('id', $chat_id)
                    ->update(['status' => 'Aborted']);
    
                    $message = "Maaf, nomor telepon anda tidak terdaftar.\n";
                    $message .= "Silahkan hubungi HRD untuk informasi lebih lanjut.";
    
                    Telegram::sendMessage([
                        'chat_id' => $chat_id,
                        'text' => $message,
                    ]);
                }
            }else if(!$data) {
                $phone_number = $update->getMessage()->getContact() !== null ? $update->getMessage()->getContact()->getPhoneNumber() : $update->getMessage()->getText();
                
                if (substr($phone_number, 0, 3) == '+628') {
                    $phone_number = substr_replace($phone_number, '08', 0, 3);
                } else if (substr($phone_number, 0, 3) == '628') {
                    $phone_number = substr_replace($phone_number, '08', 0, 3);
                }
                $phone_number = str_replace('+62', '0', $phone_number);
    
                // Check if the phone number matches any record in the database
                $staff_info = DB::table('hr_staff_info as si')
                    ->join('hr_unit as u', 'si.DEPT_NAME', '=', 'u.IdUnit')
                    ->where('Notelp', $phone_number)
                    ->first();
    
                // Handle the found staff info
                if ($staff_info) {
                    // Update or insert the phone number and other details into the telegram_session table
                    DB::table('telegram_session')
                        ->updateOrInsert([
                            'id' => $chat_id,
                        ], [
                            'first_name' => $update->getMessage()->getChat()->getFirstName(),
                            'username' => $update->getMessage()->getChat()->getUsername(),
                            'phone_number' => $phone_number,
                            'status' => 'Pending',
                            'id_staff' => $staff_info->FID,
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
    
                    // Construct and send the response message
                    $message = "Data ditemukan:\n";
                    $message .= "Nama : " . $staff_info->Nama . "\n";
                    $message .= "Departement : " . $staff_info->Namaunit . "\n";
                    $message .= "Jabatan : " . $staff_info->JABATAN . "\n";
                    $message .= "Apakah data ini benar?";
                    
                    Telegram::sendMessage([
                        'chat_id' => $chat_id,
                        'text' => $message,
                        'reply_markup' => json_encode([
                            'keyboard' => [[['text' => 'Ya'], ['text' => 'Tidak']]],
                            'resize_keyboard' => true,
                            'one_time_keyboard' => true
                        ])
                    ]);
                } else {
                    DB::table('telegram_session')
                    ->where('id', $chat_id)
                    ->update(['status' => 'Aborted']);
    
                    $message = "Maaf, nomor telepon anda tidak terdaftar.\n";
                    $message .= "Silahkan hubungi HRD untuk informasi lebih lanjut.";
    
                    Telegram::sendMessage([
                        'chat_id' => $chat_id,
                        'text' => $message,
                    ]);
                }
            }else {
                Telegram::sendMessage([
                    'chat_id' => $chat_id,
                    'text' => 'Anda sudah terdaftar, bot sudah siap digunakan'
                ]);
            }
            

            return;
        }


        $user_response = $update->getMessage()->getText();
        if ($user_response === 'Ya' && $data && $data->status === 'Pending') {
            $data = DB::table('telegram_session')
                ->where('id', $chat_id)
                ->update(['status' => 'Active']);

            if($data) {
                Telegram::sendMessage([
                    'chat_id' => $chat_id,
                    'text' => 'Terima kasih. Proses pendaftaran selesai.'
                ]);
            } else {
                Telegram::sendMessage([
                    'chat_id' => $chat_id,
                    'text' => 'Maaf, terjadi kesalahan. Silahkan coba lagi.',
                ]);
            }

            return;
        }else if($user_response === 'Tidak' && $data && $data->status === 'Pending') {
            $message = "Silahkan masukkan id staff anda dengan format berikut:\n";
            $message .= "ID:<id staff anda>\n\n";
            $message .= "Contoh:\n";
            $message .= "ID:12345";

            Telegram::sendMessage([
                'chat_id' => $chat_id,
                'text' => $message,
                'reply_markup' => json_encode([
                    'selective' => true
                ])
            ]);

            return;
        }

        if(strpos(strtolower($user_response), 'id:') !== false && $data && $data->status === 'Pending') {
            $id_staff = str_replace('id:', '', strtolower($user_response));

            $staff = DB::table('hr_staff_info as si')
                ->join('hr_unit as u', 'si.DEPT_NAME', '=', 'u.IdUnit')
                ->where('FID', $id_staff)
                ->first();

            // $data = DB::table('telegram_session')
            //     ->where('id', $chat_id)
            //     ->update(['id_staff' => $staff->FID, 'status' => 'Pending']);

            if($staff) {
                $message = "Data ditemukan:\n";
                $message .= "Nama : " . $staff->Nama . "\n";
                $message .= "Departement : " . $staff->Namaunit . "\n";
                $message .= "Jabatan : " . $staff->JABATAN. "\n";
                $message .= "Apakah data ini benar?";
                
                Telegram::sendMessage([
                    'chat_id' => $chat_id,
                    'text' => $message,
                    'reply_markup' => json_encode([
                        'keyboard' => [[['text' => 'Ya'], ['text' => 'Tidak']]],
                        'resize_keyboard' => true,
                        'one_time_keyboard' => true
                    ])
                ]);
            } else {
                DB::table('telegram_session')
                    ->where('id', $chat_id)
                    ->update(['status' => 'Aborted']);

                $message = "Maaf, nomor telepon anda tidak terdaftar.\n";
                $message .= "Silahkan hubungi HRD untuk informasi lebih lanjut.";
                Telegram::sendMessage([
                    'chat_id' => $chat_id,
                    'text' => $message,
                ]);
            }

            return;
        }

        if(strtolower($user_response) === '/start') {
            return;
        } else if (strtolower($user_response) === '/revoke') {
            return;
        } else if (strtolower($user_response) === '/help') {
            return;
        }

        Telegram::sendMessage([
            'chat_id' => $chat_id,
            'text' => 'Maaf, perintah tidak dikenali. Silahkan coba lagi.',
        ]);

        return;
        
    }

    public function getMe () {
        $response = Telegram::getMe();
        $botId = $response->getId();
        $firstName = $response->getFirstName();
        $username = $response->getUsername();

        return response()->json([
            'status' => true,
            'message' => 'Data User',
            'data' => $response,
            'first_name' => $firstName,
        ], 200);
    }

    public function webhook () {
        $response = Telegram::setWebhook(['url' => 'https://your-domain.com/your-bot-token/webhook']);

        return response()->json([
            'status' => true,
            'message' => 'Webhook has been set',
            'data' => $response
        ], 200);
    }

    public function deleteWebhook () {
        $response = Telegram::deleteWebhook();

        return response()->json([
            'status' => true,
            'message' => 'Webhook has been removed',
            'data' => $response
        ], 200);
    }

    public function getWebhookInfo () {
        $response = Telegram::getWebhookInfo();

        return response()->json([
            'status' => true,
            'message' => 'Webhook Info',
            'data' => $response
        ], 200);
    }
}
