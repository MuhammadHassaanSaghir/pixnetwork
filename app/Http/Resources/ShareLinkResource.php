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
    private $sender_id;

    public function __construct($sender_id, $link)
    {
        $this->sender_id = $sender_id;
        $this->link = $link;
    }

    public function toArray($request)
    {
        if ($this->sender_id === null) {
            $share = 'Its Public Link';
        } else {
            $share = implode(",", $this->sender_id);
        }
        return [
            'Share to' => $share,
            'Link' => $this->link,
        ];
    }
}
