<?php

namespace App\Models\Tenant;

class CarrierGuideItem extends ModelTenant
{
    protected $table = 'carrier_guide_items';

    protected $fillable = [
        'carrier_guide_id',
        'item_id',
        'item',
        'quantity',
        'unit_type_id',
        'description',
    ];

    public function getItemAttribute($value) { return is_null($value) ? null : (object)json_decode($value); }
    public function setItemAttribute($value) { $this->attributes['item'] = is_null($value) ? null : json_encode($value); }

    public function carrier_guide() { return $this->belongsTo(CarrierGuide::class); }
    public function item_rel() { return $this->belongsTo(Item::class, 'item_id'); }
}