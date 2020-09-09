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
    public $bulk = false;

    public function bulk()
    {
        $this->bulk = true;

        return $this;
    }

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

    public function toArrayObject($request) 
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

    public function toArraylist($request) 
    {
        $result =  [
            'id' => $this->id,
            'check_in_at' => $this->check_in_at,
            'check_out_at' => $this->check_out_at,
            'created_at' => $this->created_at->toDateTimeString()
        ];

        if(!$this->bulk) {
            $result = $result + [
                'id' => $this->id,
                'check_in_at' => $this->check_in_at,
                'check_out_at' => $this->check_out_at,
                'created_at' => $this->created_at->toDateTimeString(),
            ];
        }

        return $result;
    }
}
