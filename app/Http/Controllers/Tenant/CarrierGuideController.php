<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\CarrierGuide;
use App\Models\Tenant\CarrierGuideItem;
use App\Models\Tenant\Catalogs\Country;
use App\Models\Tenant\Catalogs\Department;
use App\Models\Tenant\Catalogs\District;
use App\Models\Tenant\Catalogs\IdentityDocumentType;
use App\Models\Tenant\Catalogs\Province;
use App\Models\Tenant\Catalogs\TransferReasonType;
use App\Models\Tenant\Catalogs\TransportModeType;
use App\Models\Tenant\Catalogs\UnitType;
use App\Models\Tenant\Dispatch;
use App\Models\Tenant\Establishment;
use App\Models\Tenant\Item;
use App\Models\Tenant\Person;
use App\Models\Tenant\Series;
use App\CoreFacturalo\Requests\Inputs\Functions;
use App\Models\Tenant\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Modules\Order\Models\Dispatcher;
use Modules\Order\Models\Driver;

class CarrierGuideController extends Controller
{
    public function index()
    {
        return view('tenant.carrier_guides.index');
    }

    public function create()
    {
        return view('tenant.carrier_guides.create');
    }

    public function columns()
    {
        return response()->json([
            'number'        => 'Número',
            'date_of_issue' => 'Fecha emisión',
        ]);
    }

    // DataTableDispatch llama a este endpoint al montar para cargar filtros
    public function data_table()
    {
        $customers = Person::whereType('customers')
                        ->orderBy('name')
                        ->take(20)
                        ->get()
                        ->transform(fn($p) => [
                            'id'          => $p->id,
                            'description' => $p->number . ' - ' . $p->name,
                            'name'        => $p->name,
                            'number'      => $p->number,
                        ]);

        $series = Series::where('establishment_id', auth()->user()->establishment_id)
                        ->where('document_type_id', '31')
                        ->get();

        return response()->json(compact('customers', 'series'));
    }

    public function records(Request $request)
{
    $est_id  = auth()->user()->establishment_id;
    $records = CarrierGuide::where('establishment_id', $est_id)->latest();

    if ($request->series) {
        $records->where('series', $request->series);
    }
    if ($request->number) {
        $records->where('number', $request->number);
    }
    if ($request->d_start) {
        $records->whereDate('date_of_issue', '>=', $request->d_start);
    }
    if ($request->d_end) {
        $records->whereDate('date_of_issue', '<=', $request->d_end);
    }
    if ($request->customer_id) {
        $records->where(function($q) use ($request) {
            $q->where('sender_id', $request->customer_id)
              ->orWhere('receiver_id', $request->customer_id);
        });
    }

    $data = $records->paginate(config('tenant.items_per_page'));
    $data->getCollection()->transform(fn($row) => $row->getCollectionData());

    return response()->json([
        'data' => $data->items(),
        'meta' => [
            'total'        => $data->total(),
            'per_page'     => $data->perPage(),
            'current_page' => $data->currentPage(),
            'last_page'    => $data->lastPage(),
        ],
    ]);
}
    public function tables()
    {
        // — Catálogos base —
        $transfer_reason_types = TransferReasonType::whereActive()->get();
        $transport_mode_types  = TransportModeType::whereActive()->get();
        $unit_types            = UnitType::whereActive()->get();
        $identity_document_types = IdentityDocumentType::whereActive()->get();

        // — Series tipo 31 (Guía de transportista) del establecimiento —
        $series = Series::where('establishment_id', auth()->user()->establishment_id)
                        ->where('document_type_id', '31')
                        ->get();

        // — Guías de remisión enviadas (para relacionar) —
        $dispatches = Dispatch::where('establishment_id', auth()->user()->establishment_id)
                        ->whereIn('state_type_id', ['05', '07'])
                        ->get()
                        ->map(fn($d) => ['id' => $d->id, 'number_full' => $d->series . '-' . $d->number]);

        // — Transportistas y conductores (igual que DispatchController) —
        $carriers = Dispatcher::all();   // tabla order_dispatchers
        $drivers  = Driver::all();        // tabla order_drivers

        // — Países —
        $countries = Country::whereActive()->get();

        // — Ubigeo en cascada (igual que DispatchController) —
        $locations = [];
        $departments = Department::whereActive()->get();
        foreach ($departments as $department) {
            $children_provinces = [];
            foreach ($department->provinces as $province) {
                $children_districts = [];
                foreach ($province->districts as $district) {
                    $children_districts[] = [
                        'value' => $district->id,
                        'label' => $district->id . ' - ' . $district->description,
                    ];
                }
                $children_provinces[] = [
                    'value'    => $province->id,
                    'label'    => $province->description,
                    'children' => $children_districts,
                ];
            }
            $locations[] = [
                'value'    => $department->id,
                'label'    => $department->description,
                'children' => $children_provinces,
            ];
        }
        // — Clientes/personas —
$customers = Person::whereType('customers')->whereIsEnabled()->orderBy('name')->get()
    ->map(fn($p) => ['id' => $p->id, 'description' => $p->name . ' - ' . $p->number]);

        return response()->json(compact(
    'transfer_reason_types',
    'transport_mode_types',
    'unit_types',
    'identity_document_types',
    'series',
    'dispatches',
    'carriers',
    'drivers',
    'countries',
    'locations',
    'customers'
));
    }

    public function store(Request $request)
    {
        $request->validate([
            'series'                  => 'required',
            'date_of_issue'           => 'required|date',
            'date_of_shipping'        => 'required|date',
            'sender_id'               => 'required|integer',
            'receiver_id'             => 'required|integer',
            'transport_mode_type_id'  => 'required',
            'transfer_reason_type_id' => 'required',
            'total_weight'            => 'required|numeric',
            'license_plate'           => 'required|string',
            'origin'                  => 'required|array',
            'delivery'                => 'required|array',
            'items'                   => 'required|array|min:1',
        ]);

        $establishment = Establishment::find(auth()->user()->establishment_id);
        $sender        = Person::findOrFail($request->sender_id);
        $receiver      = Person::findOrFail($request->receiver_id);

        // Siguiente número correlativo para la serie
        $last   = CarrierGuide::where('series', $request->series)->max('number');
        $number = ($last ?? 0) + 1;

        // Preparar items para Facturalo
        $items = collect($request->items)->map(function ($item_data) {
            $item = Item::findOrFail($item_data['item_id']);
            return [
                'item_id'      => $item->id,
                'item'         => $item->toArray(),
                'quantity'     => $item_data['quantity'],
                'unit_type_id' => $item_data['unit_type_id'] ?? $item->unit_type_id,
                'description'  => $item_data['description']  ?? $item->description,
            ];
        })->toArray();

        // Inputs para Facturalo (mismo patrón que dispatch)
        $inputs = [
            'type'                        => 'carrier_guide',
            'actions'                     => ['format_pdf' => 'a4'],
            'user_id'                     => auth()->id(),
            'external_id'                 => Str::uuid()->toString(),
            'establishment_id'            => $establishment->id,
            'establishment'               => $establishment->toArray(),
            'soap_type_id'                => $establishment->soap_type_id ?? '01',
            'state_type_id'               => '01',
            'ubl_version'                 => '2.1',
            'document_type_id'            => '31',
            'series'                      => $request->series,
            'number'                      => $number,
            'date_of_issue'               => $request->date_of_issue,
            'time_of_issue'               => now()->format('H:i:s'),
            'sender_id'                   => $sender->id,
            'sender'                      => $sender->toArray(),
            'receiver_id'                 => $receiver->id,
            'receiver'                    => $receiver->toArray(),
            'carrier'                     => $request->carrier   ?? [],
            'driver'                      => $request->driver    ?? null,
            'license_plate'               => $request->license_plate,
            'secondary_license_plates'    => $request->secondary_license_plates ?? null,
            'transport_mode_type_id'      => $request->transport_mode_type_id,
            'transfer_reason_type_id'     => $request->transfer_reason_type_id,
            'transfer_reason_description' => $request->transfer_reason_description ?? '',
            'date_of_shipping'            => $request->date_of_shipping,
            'transshipment_indicator'     => $request->transshipment_indicator ?? false,
            'port_code'                   => $request->port_code  ?? null,
            'unit_type_id'                => $request->unit_type_id ?? 'KGM',
            'total_weight'                => $request->total_weight,
            'packages_number'             => $request->packages_number ?? null,
            'container_number'            => $request->container_number ?? null,
            'origin'                      => $request->origin,
            'delivery'                    => $request->delivery,
            'dispatch_id'                 => $request->dispatch_id ?? null,
            'observations'                => $request->observations ?? '',
            'items'                       => $items,
            'filename'                    => Functions::filename(Company::active(), '31', $request->series, $number),
        ];

        try {
    $fact = \DB::connection('tenant')->transaction(function () use ($inputs) {
        $facturalo = new \App\CoreFacturalo\Facturalo();
        $facturalo->save($inputs);
        return $facturalo;
    });

    $fact->createPdf();

    $document = $fact->getDocument();
    $document->has_pdf = true;
    $document->save();

    return response()->json([
        'success' => true,
        'message' => "Se creó la guía de transportista {$document->series}-{$document->number}",
        'data'    => ['id' => $document->id],
    ]);
} catch (\Exception $e) {
    return response()->json([
        'success' => false,
        'message' => $e->getMessage(),
        'line'    => $e->getLine(),
        'file'    => $e->getFile(),
    ], 500);
}
    }

    public function record($id)
    {
        $carrier_guide = CarrierGuide::with('items')->findOrFail($id);
        return response()->json([
            'success' => true,
            'data'    => $carrier_guide->getCollectionData(),
        ]);
    }

    public function destroy($id)
    {
        $carrier_guide = CarrierGuide::findOrFail($id);

        if (!in_array($carrier_guide->state_type_id, ['01', '09'])) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar una guía ya enviada a SUNAT',
            ]);
        }

        $carrier_guide->items()->delete();
        $carrier_guide->delete();

        return response()->json([
            'success' => true,
            'message' => 'Guía eliminada correctamente',
        ]);
    }

    public function searchPersons(Request $request)
    {
        $persons = Person::where(function ($q) use ($request) {
                        $q->where('name',   'like', '%' . $request->search . '%')
                          ->orWhere('number','like', '%' . $request->search . '%');
                    })
                    ->whereIn('identity_document_type_id', ['6', '1', '4', '7'])
                    ->limit(20)
                    ->get()
                    ->map(fn($p) => [
                        'id'     => $p->id,
                        'name'   => $p->name,
                        'number' => $p->number,
                        'identity_document_type_id' => $p->identity_document_type_id,
                    ]);

        return response()->json($persons);
    }

    public function searchItems(Request $request)
    {
        $items = Item::where(function ($q) use ($request) {
                        $q->where('description', 'like', '%' . $request->search . '%')
                          ->orWhere('internal_id','like', '%' . $request->search . '%');
                    })
                    ->where('item_type_id', '01')
                    ->limit(20)
                    ->get()
                    ->map(fn($i) => [
                        'id'           => $i->id,
                        'description'  => $i->description,
                        'internal_id'  => $i->internal_id,
                        'unit_type_id' => $i->unit_type_id,
                    ]);

        return response()->json($items);
    }
}