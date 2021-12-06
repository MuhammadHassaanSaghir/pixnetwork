<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ImageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        if ($this->privacy == 0) {
            $privacy = 'Public';
        } elseif ($this->privacy == 1) {
            $privacy = 'Private';
        }
        return [
            'Name' => $this->image_name,
            'Image' => $this->image_path,
            'Privacy' => $privacy,
        ];
    }
}
