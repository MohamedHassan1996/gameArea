<?php

namespace App\Http\Resources\Api\V1\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LoggedInUserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            /*'roles' => $this->getRoleNames(),
            'permissions' => $this->getAllPermissions()
                ->pluck('name')
                ->values(),*/
        ];
    }
}
