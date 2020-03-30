<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AuthResource extends JsonResource
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
            'access_token' => $this['access']['access_token'],
            'refresh_token' => $this['access']['refresh_token'],
        ], ['user_data' => (new UserResource($this['user']))->toArray($request)]);
    }
}
