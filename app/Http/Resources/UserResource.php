<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $result = [
            'id'        => $this->id,
            'name'      => $this->name,
            'email' => $this->email,
            'phone'     => $this->phone,
            'employee_id' => $this->employee_id
        ];

        return $result;
    }
}
