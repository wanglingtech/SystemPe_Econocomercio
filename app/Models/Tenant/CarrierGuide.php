<?php

namespace App\Models\Tenant;

use App\Models\Tenant\Catalogs\TransferReasonType;
use App\Models\Tenant\Catalogs\TransportModeType;
use App\Models\Tenant\Catalogs\UnitType;
use Illuminate\Database\Eloquent\Builder;

/**
 * Modelo para Guía de Transportista
 * Documento tipo 31 - SUNAT GRE formato transportista
 */
class CarrierGuide extends ModelTenant
{
    protected $table = 'carrier_guides';

    protected $fillable = [
        'user_id',
        'external_id',
        'establishment_id',
        'establishment',
        'soap_type_id',
        'state_type_id',
        'ubl_version',
        'document_type_id',
        'series',
        'number',
        'date_of_issue',
        'time_of_issue',
        'sender_id',
        'sender',
        'receiver_id',
        'receiver',
        'carrier',
        'driver',
        'license_plate',
        'secondary_license_plates',
        'transport_mode_type_id',
        'transfer_reason_type_id',
        'transfer_reason_description',
        'date_of_shipping',
        'transshipment_indicator',
        'port_code',
        'unit_type_id',
        'total_weight',
        'packages_number',
        'container_number',
        'origin',
        'delivery',
        'dispatch_id',
        'observations',
        'legends',
        'filename',
        'hash',
        'has_xml',
        'has_pdf',
        'has_cdr',
        'soap_shipping_response',
    ];

    protected $casts = [
        'date_of_issue'   => 'date',
        'date_of_shipping' => 'date',
        'transshipment_indicator' => 'bool',
        'has_xml' => 'bool',
        'has_pdf' => 'bool',
        'has_cdr' => 'bool',
    ];

    // JSON accessors/mutators
    public function getEstablishmentAttribute($value) { return is_null($value) ? null : (object)json_decode($value); }
    public function setEstablishmentAttribute($value) { $this->attributes['establishment'] = is_null($value) ? null : json_encode($value); }

    public function getSenderAttribute($value) { return is_null($value) ? null : (object)json_decode($value); }
    public function setSenderAttribute($value) { $this->attributes['sender'] = is_null($value) ? null : json_encode($value); }

    public function getReceiverAttribute($value) { return is_null($value) ? null : (object)json_decode($value); }
    public function setReceiverAttribute($value) { $this->attributes['receiver'] = is_null($value) ? null : json_encode($value); }

    public function getCarrierAttribute($value) { return is_null($value) ? null : (object)json_decode($value); }
    public function setCarrierAttribute($value) { $this->attributes['carrier'] = is_null($value) ? null : json_encode($value); }

    public function getDriverAttribute($value) { return is_null($value) ? null : (object)json_decode($value); }
    public function setDriverAttribute($value) { $this->attributes['driver'] = is_null($value) ? null : json_encode($value); }

    public function getOriginAttribute($value) { return is_null($value) ? null : (object)json_decode($value); }
    public function setOriginAttribute($value) { $this->attributes['origin'] = is_null($value) ? null : json_encode($value); }

    public function getDeliveryAttribute($value) { return is_null($value) ? null : (object)json_decode($value); }
    public function setDeliveryAttribute($value) { $this->attributes['delivery'] = is_null($value) ? null : json_encode($value); }

    public function getLegendsAttribute($value) { return is_null($value) ? null : (object)json_decode($value); }
    public function setLegendsAttribute($value) { $this->attributes['legends'] = is_null($value) ? null : json_encode($value); }

    public function getSecondaryLicensePlatesAttribute($value) { return is_null($value) ? null : (object)json_decode($value); }
    public function setSecondaryLicensePlatesAttribute($value) { $this->attributes['secondary_license_plates'] = is_null($value) ? null : json_encode($value); }

    public function getSoapShippingResponseAttribute($value) { return is_null($value) ? null : (object)json_decode($value); }
    public function setSoapShippingResponseAttribute($value) { $this->attributes['soap_shipping_response'] = is_null($value) ? null : json_encode($value); }

    // Relaciones
    public function user() { return $this->belongsTo(User::class); }
    public function establishment_rel() { return $this->belongsTo(Establishment::class, 'establishment_id'); }
    public function soap_type() { return $this->belongsTo(SoapType::class); }
    public function state_type() { return $this->belongsTo(StateType::class); }
    public function sender_person() { return $this->belongsTo(Person::class, 'sender_id'); }
    public function receiver_person() { return $this->belongsTo(Person::class, 'receiver_id'); }
    public function unit_type() { return $this->belongsTo(UnitType::class, 'unit_type_id'); }
    public function transport_mode_type() { return $this->belongsTo(TransportModeType::class, 'transport_mode_type_id'); }
    public function transfer_reason_type() { return $this->belongsTo(TransferReasonType::class, 'transfer_reason_type_id'); }
    public function items() { return $this->hasMany(CarrierGuideItem::class); }
    public function dispatch() { return $this->belongsTo(Dispatch::class, 'dispatch_id'); }

    // Número completo
    public function getNumberFullAttribute()
    {
        return $this->series . '-' . $this->number;
    }

    // Rutas de descarga
    public function getDownloadExternalXmlAttribute()
    {
        return route('tenant.download.external_id', ['model' => 'carrier_guide', 'type' => 'xml', 'external_id' => $this->external_id]);
    }

    public function getDownloadExternalPdfAttribute()
    {
        return route('tenant.download.external_id', ['model' => 'carrier_guide', 'type' => 'pdf', 'external_id' => $this->external_id]);
    }

    public function getDownloadExternalCdrAttribute()
    {
        return route('tenant.download.external_id', ['model' => 'carrier_guide', 'type' => 'cdr', 'external_id' => $this->external_id]);
    }

    public function scopeWhereTypeUser($query)
    {
        $user = auth()->user();
        return ($user->type == 'seller') ? $query->where('user_id', $user->id) : null;
    }

    public function getCollectionData()
    {
        return [
            'id'                      => $this->id,
            'external_id'             => $this->external_id,
            'soap_type_id'            => $this->soap_type_id,
            'date_of_issue'           => $this->date_of_issue->format('Y-m-d'),
            'number'                  => $this->number_full,
            'series'                  => $this->series,
            'sender_name'             => $this->sender->name ?? '',
            'receiver_name'           => $this->receiver->name ?? '',
            'carrier_name'            => $this->carrier->name ?? '',
            'license_plate'           => $this->license_plate,
            'date_of_shipping'        => $this->date_of_shipping->format('Y-m-d'),
            'state_type_id'           => $this->state_type_id,
            'state_type_description'  => $this->state_type->description,
            'has_xml'                 => $this->has_xml,
            'has_pdf'                 => $this->has_pdf,
            'has_cdr'                 => in_array($this->state_type_id, ['05', '07']),
            'download_external_xml'   => $this->download_external_xml,
            'download_external_pdf'   => $this->download_external_pdf,
            'download_external_cdr'   => $this->download_external_cdr,
            'transfer_reason_type'    => $this->transfer_reason_type,
            'total_weight'            => $this->total_weight,
            'soap_shipping_response'  => $this->soap_shipping_response,
            'created_at'              => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}