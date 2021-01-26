<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ModelsResource extends JsonResource
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
            'version'        => $this->id,
            'train'      => $this->train,
            'train_model' => $this->train_model,
            'label'     => $this->label,
        ];

        return $result;
    }
}
