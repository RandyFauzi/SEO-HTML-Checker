<?php

namespace App\Traits;

use Vinkla\Hashids\Facades\Hashids;

trait HasHashId
{
    public function getRouteKey()
    {
        return Hashids::encode($this->getKey());
    }

    public function resolveRouteBinding($value, $field = null)
    {
        if (empty($value)) {
            return null;
        }

        $decoded = Hashids::decode($value);
        
        if (empty($decoded)) {
            return null;
        }

        return $this->where($field ?? $this->getRouteKeyName(), $decoded[0])->first();
    }
}
