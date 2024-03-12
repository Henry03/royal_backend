<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanExpiredEmployeeToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clean:expired-employee-token';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete records with expired token';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $records = DB::table('login_access')
            ->where('exp_date', '<', now())
            ->update(['otp' => null, 'token' => null]);

        $this->info("Deleted {$records} records with expired token");
    }
}
