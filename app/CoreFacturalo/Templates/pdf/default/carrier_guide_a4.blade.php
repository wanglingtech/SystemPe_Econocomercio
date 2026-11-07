@php
    $establishment = (object) (is_array($document->establishment) ? $document->establishment : (array) $document->establishment);
    $sender        = (object) (is_array($document->sender)        ? $document->sender        : (array) $document->sender);
    $receiver      = (object) (is_array($document->receiver)      ? $document->receiver      : (array) $document->receiver);
    $carrier       = (object) (is_array($document->carrier)       ? $document->carrier       : (array) $document->carrier);
    $driver        = $document->driver ? (object) (is_array($document->driver) ? $document->driver : (array) $document->driver) : null;
    $origin        = (object) (is_array($document->origin)        ? $document->origin        : (array) $document->origin);
    $delivery      = (object) (is_array($document->delivery)      ? $document->delivery      : (array) $document->delivery);
    $document_number = $document->series.'-'.str_pad($document->number, 8, '0', STR_PAD_LEFT);
@endphp
<html>
<head></head>
<body>

<table class="full-width">
    <tr>
        @if($company->logo)
            <td width="10%">
                <img src="data:{{mime_content_type(public_path("storage/uploads/logos/{$company->logo}"))}};base64, {{base64_encode(file_get_contents(public_path("storage/uploads/logos/{$company->logo}")))}}" alt="{{$company->name}}" class="company_logo" style="max-width: 300px">
            </td>
        @else
            <td width="10%"></td>
        @endif
        <td width="50%" class="pl-3">
            <div class="text-left">
                <h3>{{ $company->name }}</h3>
                <h4>RUC {{ $company->number }}</h4>
                <h5 style="text-transform: uppercase;">
                    {{ isset($establishment->address) && $establishment->address !== '-' ? $establishment->address : '' }}
                </h5>
                <h5>{{ isset($establishment->email) && $establishment->email !== '-' ? $establishment->email : '' }}</h5>
                <h5>{{ isset($establishment->telephone) && $establishment->telephone !== '-' ? $establishment->telephone : '' }}</h5>
            </div>
        </td>
        <td width="40%" class="border-box p-4 text-center">
            <h4 class="text-center">GUÍA DE REMISIÓN TRANSPORTISTA</h4>
            <h3 class="text-center">{{ $document_number }}</h3>
        </td>
    </tr>
</table>

<table class="full-width border-box mt-10 mb-10">
    <thead>
        <tr><th class="border-bottom text-left" colspan="2">DATOS GENERALES</th></tr>
    </thead>
    <tbody>
        <tr>
            <td>Fecha Emisión: {{ $document->date_of_issue instanceof \Carbon\Carbon ? $document->date_of_issue->format('Y-m-d') : $document->date_of_issue }}</td>
            <td>Fecha Traslado: {{ $document->date_of_shipping instanceof \Carbon\Carbon ? $document->date_of_shipping->format('Y-m-d') : $document->date_of_shipping }}</td>
        </tr>
        <tr>
            <td>Motivo de Traslado: {{ optional($document->transfer_reason_type)->description ?? $document->transfer_reason_type_id }}</td>
            <td>Modalidad: {{ optional($document->transport_mode_type)->description ?? $document->transport_mode_type_id }}</td>
        </tr>
        @if($document->transfer_reason_description)
        <tr>
            <td colspan="2">Descripción: {{ $document->transfer_reason_description }}</td>
        </tr>
        @endif
        <tr>
            <td>Peso Bruto Total ({{ $document->unit_type_id }}): {{ $document->total_weight }}</td>
            @if($document->packages_number)
            <td>N° Bultos: {{ $document->packages_number }}</td>
            @endif
        </tr>
    </tbody>
</table>

<table class="full-width border-box mt-10 mb-10">
    <thead>
        <tr><th class="border-bottom text-left" colspan="2">REMITENTE Y DESTINATARIO</th></tr>
    </thead>
    <tbody>
        <tr>
            <td><strong>Remitente:</strong> {{ $sender->name ?? '' }}</td>
            <td><strong>Destinatario:</strong> {{ $receiver->name ?? '' }}</td>
        </tr>
        <tr>
            <td>Doc.: {{ $sender->number ?? '' }}</td>
            <td>Doc.: {{ $receiver->number ?? '' }}</td>
        </tr>
    </tbody>
</table>

<table class="full-width border-box mt-10 mb-10">
    <thead>
        <tr><th class="border-bottom text-left" colspan="2">PUNTOS DE PARTIDA Y LLEGADA</th></tr>
    </thead>
    <tbody>
        <tr>
            <td><strong>P. Partida:</strong> {{ $origin->address ?? '' }}</td>
            <td><strong>P. Llegada:</strong> {{ $delivery->address ?? '' }}</td>
        </tr>
    </tbody>
</table>

<table class="full-width border-box mt-10 mb-10">
    <thead>
        <tr><th class="border-bottom text-left" colspan="2">DATOS DEL TRANSPORTISTA</th></tr>
    </thead>
    <tbody>
        <tr>
            <td>RUC: {{ $carrier->number ?? '' }}</td>
            <td>Razón Social: {{ $carrier->name ?? '' }}</td>
        </tr>
        <tr>
            <td>Placa: {{ $document->license_plate }}</td>
            @if($document->secondary_license_plates && !empty(((object)$document->secondary_license_plates)->semitrailer))
            <td>Placa semirremolque: {{ ((object)$document->secondary_license_plates)->semitrailer }}</td>
            @else
            <td></td>
            @endif
        </tr>
        @if($driver)
        <tr>
            <td>Conductor DNI: {{ $driver->number ?? '' }}</td>
            <td>Nombre: {{ $driver->name ?? '' }}</td>
        </tr>
        @if(isset($driver->license) && $driver->license)
        <tr>
            <td colspan="2">Licencia: {{ $driver->license }}</td>
        </tr>
        @endif
        @endif
    </tbody>
</table>

<table class="full-width border-box mt-10 mb-10">
    <thead>
        <tr>
            <th class="border-top-bottom text-center">Item</th>
            <th class="border-top-bottom text-left">Descripción</th>
            <th class="border-top-bottom text-center">Unidad</th>
            <th class="border-top-bottom text-right">Cantidad</th>
        </tr>
    </thead>
    <tbody>
        @foreach($document->items as $row)
        <tr>
            <td class="text-center">{{ $loop->iteration }}</td>
            <td class="text-left">{{ $row->description }}</td>
            <td class="text-center">{{ $row->unit_type_id }}</td>
            <td class="text-right">
                @if((int)$row->quantity != $row->quantity)
                    {{ $row->quantity }}
                @else
                    {{ number_format($row->quantity, 0) }}
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

@if($document->observations)
<table class="full-width border-box mt-10 mb-10">
    <tr>
        <td class="text-bold border-bottom font-bold">OBSERVACIONES</td>
    </tr>
    <tr>
        <td>{{ $document->observations }}</td>
    </tr>
</table>
@endif

</body>
</html>