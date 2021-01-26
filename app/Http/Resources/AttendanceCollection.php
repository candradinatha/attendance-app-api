<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class AttendanceCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'present' => $this['present'],
            'absent' => $this['absent'],
            'late' => $this['late'],
            'start_working_hour' => $this['start_working_hour'],
            'end_working_hour' => $this['end_working_hour'],
            'attendances' => $this['attendances']->map(function ($model) use ($request){
                return (new AttendanceResource($model))->bulk()->toArrayList($request);
            })
        ];
        // return $this->collection->map(function ($model) use ($request) {
        //     return (new AttendanceResource($model))->bulk()->toArrayList($request);
        // });
    }
}
