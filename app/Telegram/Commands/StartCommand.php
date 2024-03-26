<?php

namespace App\Telegram\Commands;

use Illuminate\Support\Facades\DB;
use Telegram\Bot\Commands\Command;
use Telegram\Bot\Laravel\Facades\Telegram;

class StartCommand extends Command
{
    protected string $name = 'start';
    protected string $description = 'Start the bot';

    public function handle()
    {
        $chat_id = $this->getUpdate()->getMessage()->getChat()->getId();
        $username = $this->getUpdate()->getMessage()->getChat()->getFirstName();

        Telegram::sendMessage([
            'chat_id' => $chat_id,
            'text' => 'Halo '.$username.', selamat datang di bot kami'
        ]);

        $data = DB::table('telegram_session')
            ->where('id', $chat_id)
            ->where('status', 'Active')
            ->first();
        
        if($data) {
            Telegram::sendMessage([
                'chat_id' => $chat_id,
                'text' => 'Anda sudah terdaftar, bot sudah siap digunakan',
            ]);
        } else {
            Telegram::sendMessage([
                'chat_id' => $chat_id,
                'text' => 'Silahkan masukkan nomor telepon anda yang terdaftar',
                'reply_markup' => json_encode([
                    'keyboard' => [[['text' => 'Kirim Nomor Telepon', 'request_contact' => true]]],
                    'resize_keyboard' => true,
                    'one_time_keyboard' => true
                ])
            ]);
        }
    }
}