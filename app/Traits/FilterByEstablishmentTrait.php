<?php
namespace App\Traits;

trait FilterByEstablishmentTrait
{
    public function getEstablishmentId()
    {
        return auth()->user()->establishment_id;
    }

    public function applyEstablishmentFilter($query, $column = 'establishment_id')
    {
        return $query->where($column, $this->getEstablishmentId());
    }
}