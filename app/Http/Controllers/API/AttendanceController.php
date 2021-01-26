<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Attendance;
use App\WorkingHour;
use App\User;
use Illuminate\Http\Request;
use App\Http\Resources\AttendanceResource;
use App\Http\Requests\AttendanceRequest;
use App\Http\Resources\AttendanceCollection;



class AttendanceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $user = auth()->user();
        $attendance = Attendance::where('user_id', $user->id)->orderBy('created_at', 'desc')->first();
        $allAbsent = Attendance::whereDate('created_at', \Carbon\Carbon::today())->whereNull('check_in_at')->count();
        $allAttendance = Attendance::whereDate('created_at', \Carbon\Carbon::today())->whereNotNull('check_in_at')->count();

        return new AttendanceResource([
            'attendance' => $attendance,
            'all_attendance' => $allAttendance,
            'all_absent' => $allAbsent,
            'user' => $user
        ]);
        
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Attendance  $attendance
     * @return \Illuminate\Http\Response
     */
    public function show(Attendance $attendance)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Attendance  $attendance
     * @return \Illuminate\Http\Response
     */
    public function edit(Attendance $attendance)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Attendance  $attendance
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Attendance $attendance)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Attendance  $attendance
     * @return \Illuminate\Http\Response
     */
    public function destroy(Attendance $attendance)
    {
        //
    }

    public function checkIn(Attendance $attendance)
    {
        $attendance->check_in_at = \Carbon\Carbon::now();
        $allAbsent = Attendance::whereDate('created_at', \Carbon\Carbon::today())->whereNull('check_in_at')->count();
        $allAttendance = Attendance::whereDate('created_at', \Carbon\Carbon::today())->whereNotNull('check_in_at')->count();
        $user = User::where('id', $attendance->user_id)->firstOrFail();

        $attendance->save();
        $mAttendance = Attendance::where('id', $attendance->id)->first();

        return new AttendanceResource([
            'attendance' => $mAttendance,
            'all_attendance' => $allAttendance,
            'all_absent' => $allAbsent,
            'user' => $user
        ]);
    }

    public function checkOut(Attendance $attendance)
    {
        $attendance->check_out_at = \Carbon\Carbon::now();
        $allAbsent = Attendance::whereDate('created_at', \Carbon\Carbon::today())->whereNull('check_in_at')->count();
        $allAttendance = Attendance::whereDate('created_at', \Carbon\Carbon::today())->whereNotNull('check_in_at')->count();
        $user = User::where('id', $attendance->user_id)->firstOrFail();

        $attendance->save();
        $mAttendance = Attendance::where('id', $attendance->id)->first();

        return new AttendanceResource([
            'attendance' => $mAttendance,
            'all_attendance' => $allAttendance,
            'all_absent' => $allAbsent,
            'user' => $user
        ]);
    }

    public function monthlyAttendance() {
        $user = auth()->user();
        $month = date('m');
        $present = 0;
        $late = 0;
        $absent = 0;
        $workingHour = WorkingHour::first();
        $attendance = Attendance::where('user_id', $user->id)->whereMonth('created_at', '=', $month)->orderBy('created_at', 'asc')->get();
        foreach ($attendance as &$att) {
            if ($att->check_in_at == null && $att->check_out_at == null) {
                $absent = $absent + 1;
            } else {
                $checkInTime = date('H:i:s', strtotime($att->check_in_at));
                if ($checkInTime > $workingHour->start_time){
                    $late = $late + 1;
                } else {
                    $present = $present + 1;
                }
            }
        }
        // dd($attendance);
        // dd(\Carbon\Carbon::now()->subDays(3));
        return new AttendanceCollection([
            'present' => $present,
            'late' => $late,
            'absent' => $absent,
            'attendances' => $attendance,
            'start_working_hour' => $workingHour->start_time,
            'end_working_hour' => $workingHour->end_time
        ]);
        // return new AttendanceCollection($attendance);
    }

    public function getAttendanceByLabel($label) {
        $user = User::where('employee_id', $label)->firstOrFail();
        $attendance = Attendance::where('user_id', $user->id)->orderBy('created_at', 'desc')->first();
        $allAbsent = Attendance::whereDate('created_at', \Carbon\Carbon::today())->whereNull('check_in_at')->count();
        $allAttendance = Attendance::whereDate('created_at', \Carbon\Carbon::today())->whereNotNull('check_in_at')->count();
        
        return new AttendanceResource([
            'attendance' => $attendance,
            'all_attendance' => $allAttendance,
            'all_absent' => $allAbsent,
            'user' => $user
        ]);
    }

}
