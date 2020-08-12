<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\User;
use App\Attendance;

class CreateDailyAttendance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create daily attendance';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //
        $users = User::all();
        foreach($users as $user)
        {
            $attendance = new Attendance;
            $attendance->user_id = $user->id;
            $attendance->save();
        }
    }
}
