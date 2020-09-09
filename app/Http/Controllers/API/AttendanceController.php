<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Attendance;
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
            'all_absent' => $allAbsent
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

        $attendance->save();

        return $this->index();
    }

    public function checkOut(Attendance $attendance)
    {
        $attendance->check_out_at = \Carbon\Carbon::now();
        $allAbsent = Attendance::whereDate('created_at', \Carbon\Carbon::today())->whereNull('check_in_at')->count();
        $allAttendance = Attendance::whereDate('created_at', \Carbon\Carbon::today())->whereNotNull('check_in_at')->count();

        $attendance->save();

        return $this->index();
    }

    public function weeklyAttendance() {
        $user = auth()->user();
        $attendance = Attendance::where('user_id', $user->id)->orderBy('created_at', 'desc')->limit(7)->get();
        // dd($attendance);
        return new AttendanceCollection($attendance);
    }

}
