<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Ramsey\Uuid\Nonstandard\Uuid;

class ContactResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => 'contact',
            'id' => Uuid::uuid4()->toString(),
            'attributes' => [
                'message' => 'Contacto guardado de forma éxitosaa',
                'name' => $this->name,
                'phone_number' => $this->phone,
                'email' => $this->email,
                'company' => $this->company,

            ],
            'links' => [
                'self' => (url()->current().'/'.$this->id),
            ],
        ];
    }
}
