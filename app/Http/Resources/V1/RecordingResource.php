<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RecordingResource extends JsonResource
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
            'url' => asset('storage/'. $this->file_location),
            'description' => $this->description,
            'fileName' => $this->file_name,
            'fileSize' => $this->file_size,
            'thumbnail' => $this->thumbnail ? asset('storage/'. $this->thumbnail) : null,
            'slug' => $this->slug,
            'createdAt' => $this->created_at
        ];
    }
}
