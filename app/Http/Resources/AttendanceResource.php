<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this['attendance']->id,
            'check_in_at' => $this['attendance']->check_in_at,
            'check_out_at' => $this['attendance']->check_out_at,
            'created_at' => $this['attendance']->created_at->toDateTimeString(),
            'all_attendance' => $this['all_attendance'],
            'all_absent' => $this['all_absent']
        ];
    }
}
