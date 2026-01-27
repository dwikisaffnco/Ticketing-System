<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GuideCategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'icon' => $this->icon,
            'order' => $this->order,
            'guides_count' => $this->whenCounted('guides'),
            $this->mergeWhen($this->relationLoaded('guides'), [
                'guides' => GuideResource::collection($this->guides),
            ]),
        ];
    }
}
