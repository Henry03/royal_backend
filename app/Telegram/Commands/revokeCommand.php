<?php

namespace App\Telegram\Commands;

use Illuminate\Support\Facades\DB;
use Telegram\Bot\Commands\Command;
use Telegram\Bot\Laravel\Facades\Telegram;

class RevokeCommand extends Command
{
    protected string $name = 'revoke';
    protected string $description = 'Revoke the bot';

    public function handle()
    {
        $chat_id = $this->getUpdate()->getMessage()->getChat()->getId();
        $username = $this->getUpdate()->getMessage()->getChat()->getFirstName();

        $data = DB::table('telegram_session')
            ->where('id', $chat_id)
            ->where('status', 'Active')
            ->update(['status' => 'Inactive']);
        
        if($data) {
            Telegram::sendMessage([
                'chat_id' => $chat_id,
                'text' => 'Anda sudah tidak terdaftar, bot sudah tidak bisa digunakan lagi'
            ]);
            Telegram::sendMessage([
                'chat_id' => $chat_id,
                'text' => 'Ketik /start untuk mendaftar kembali'
            ]);
        } else {
            Telegram::sendMessage([
                'chat_id' => $chat_id,
                'text' => 'Maaf, terjadi kesalahan. Silahkan coba lagi.',
            ]);
        }

        return;
    }
}