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
        return array_merge([
            'check_in_at' => $this['attendance']['check_in_at'],
            'check_out_at' => $this['attendance']['check_out_at'],
            'created_at' => $this['attendance']['created_at']
        ], ['user_data' => (new UserResource($this['user']))->toArray($request)]);
    }
}
