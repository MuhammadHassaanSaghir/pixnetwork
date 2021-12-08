<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ShareLinkResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    private $link;

    public function __construct($resource, $link)
    {
        // Ensure we call the parent constructor
        parent::__construct($resource);
        $this->resource = $resource;
        $this->link = $link; // $apple param passed
    }

    public function toArray($request)
    {
        return [
            'User' => $this->user_id,
            'Image' => $this->image_id,
            'Link' => $this->link,
            'Visibility' => $this->visibility,
            'Email' => $this->email,
        ];
    }
}
