<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

// Ejecutar desde la raíz: php scripts\cargar-datos-prueba-tenant.php tenancy_econocomercio
require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$database = isset($argv[1]) ? $argv[1] : 'tenancy_econocomercio';
if (strpos($database, 'tenancy_') !== 0) {
    throw new InvalidArgumentException('Solo se permite cargar una base tenant con prefijo tenancy_.');
}

config(['database.connections.tenant.database' => $database]);
DB::purge('tenant');
$db = DB::connection('tenant');

$now = date('Y-m-d H:i:s');
$today = date('Y-m-d');
$time = date('H:i:s');

$requiredTables = [
    'users', 'establishments', 'series', 'persons', 'items', 'warehouses',
    'item_warehouse', 'inventories', 'purchases', 'purchase_items', 'documents',
    'document_items', 'document_payments', 'expense_types', 'expenses',
    'expense_items', 'payment_method_types', 'cash', 'cash_documents',
];

foreach ($requiredTables as $table) {
    if (!$db->getSchemaBuilder()->hasTable($table)) {
        throw new RuntimeException("Falta la tabla requerida: {$table}");
    }
}

$one = static function ($table, $column, $value) use ($db) {
    return $db->table($table)->where($column, $value)->first();
};

$user = $db->table('users')->orderBy('id')->first();
$establishment = $db->table('establishments')->orderBy('id')->first();
if (!$user || !$establishment) {
    throw new RuntimeException('El tenant debe tener al menos un usuario y un establecimiento.');
}

$catalog = static function ($table, $id) use ($one) {
    $row = $one($table, 'id', $id);
    if (!$row) {
        throw new RuntimeException("No existe {$table}.id={$id}.");
    }
    return $row;
};

$currency = $catalog('cat_currency_types', 'PEN');
$affectation = $catalog('cat_affectation_igv_types', '10');
$priceType = $catalog('cat_price_types', '01');
$unitType = $catalog('cat_unit_types', 'NIU');
$itemType = $catalog('item_types', '01');
$soap = $catalog('soap_types', '01');
$state = $catalog('state_types', '01');
$groupInvoice = $catalog('groups', '01');
$groupReceipt = $catalog('groups', '02');
$documentInvoice = $catalog('cat_document_types', '01');
$documentReceipt = $catalog('cat_document_types', '03');
$paymentMethod = $db->table('payment_method_types')->where('id', '01')->first();
$expenseType = $db->table('expense_types')->orderBy('id')->first();
if (!$paymentMethod || !$expenseType) {
    throw new RuntimeException('Faltan catálogos de método de pago o tipo de gasto.');
}

$series = [];
foreach ([
    ['01', 'F001'],
    ['03', 'B001'],
] as $seriesData) {
    $row = $db->table('series')
        ->where('establishment_id', $establishment->id)
        ->where('document_type_id', $seriesData[0])
        ->where('number', $seriesData[1])
        ->first();

    if (!$row) {
        $id = $db->table('series')->insertGetId([
            'establishment_id' => $establishment->id,
            'document_type_id' => $seriesData[0],
            'number' => $seriesData[1],
            'contingency' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $row = $db->table('series')->find($id);
    }

    $series[$seriesData[0]] = $row;
}

$customers = [];
$suppliers = [];
for ($i = 1; $i <= 20; $i++) {
    $customer = $db->table('persons')->where('number', '70000000' . str_pad($i, 2, '0', STR_PAD_LEFT))->first();
    if (!$customer) {
        $id = $db->table('persons')->insertGetId([
            'type' => 'customers',
            'identity_document_type_id' => '1',
            'number' => '70000000' . str_pad($i, 2, '0', STR_PAD_LEFT),
            'name' => "TEST Cliente {$i}",
            'trade_name' => "TEST Cliente {$i}",
            'country_id' => 'PE',
            'department_id' => '15',
            'province_id' => '1501',
            'district_id' => '150101',
            'address' => "Av. Prueba {$i}",
            'email' => "cliente{$i}@test.local",
            'telephone' => '900000' . str_pad($i, 3, '0', STR_PAD_LEFT),
            'enabled' => 1,
            'status' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $customer = $db->table('persons')->find($id);
    }
    $customers[] = $customer;

    $supplierNumber = '20600000' . str_pad($i, 3, '0', STR_PAD_LEFT);
    $supplier = $db->table('persons')->where('number', $supplierNumber)->first();
    if (!$supplier) {
        $id = $db->table('persons')->insertGetId([
            'type' => 'suppliers',
            'identity_document_type_id' => '6',
            'number' => $supplierNumber,
            'name' => "TEST Proveedor {$i} SAC",
            'trade_name' => "TEST Proveedor {$i}",
            'country_id' => 'PE',
            'department_id' => '15',
            'province_id' => '1501',
            'district_id' => '150101',
            'address' => "Jr. Proveedores {$i}",
            'email' => "proveedor{$i}@test.local",
            'telephone' => '910000' . str_pad($i, 3, '0', STR_PAD_LEFT),
            'enabled' => 1,
            'status' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $supplier = $db->table('persons')->find($id);
    }
    $suppliers[] = $supplier;
}

$items = [];
$warehouses = [];
for ($i = 1; $i <= 20; $i++) {
    $itemCode = 'TEST-ITEM-' . str_pad($i, 2, '0', STR_PAD_LEFT);
    $item = $db->table('items')->where('item_code', $itemCode)->first();
    if (!$item) {
        $id = $db->table('items')->insertGetId([
            'name' => "Producto de prueba {$i}",
            'description' => "Producto de prueba {$i}",
            'item_type_id' => $itemType->id,
            'internal_id' => $itemCode,
            'item_code' => $itemCode,
            'unit_type_id' => $unitType->id,
            'currency_type_id' => $currency->id,
            'sale_unit_price' => 118 + $i,
            'purchase_unit_price' => 100 + $i,
            'sale_affectation_igv_type_id' => $affectation->id,
            'purchase_affectation_igv_type_id' => $affectation->id,
            'has_igv' => 1,
            'purchase_has_igv' => 1,
            'stock' => 20,
            'stock_min' => 5,
            'active' => 1,
            'status' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $item = $db->table('items')->find($id);
    }
    $items[] = $item;

    $warehouseName = 'TEST Almacen ' . str_pad($i, 2, '0', STR_PAD_LEFT);
    $warehouse = $db->table('warehouses')
        ->where('establishment_id', $establishment->id)
        ->where('description', $warehouseName)
        ->first();
    if (!$warehouse) {
        $id = $db->table('warehouses')->insertGetId([
            'establishment_id' => $establishment->id,
            'description' => $warehouseName,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $warehouse = $db->table('warehouses')->find($id);
    }
    $warehouses[] = $warehouse;

    $itemWarehouse = $db->table('item_warehouse')
        ->where('item_id', $item->id)
        ->where('warehouse_id', $warehouse->id)
        ->first();
    if (!$itemWarehouse) {
        $db->table('item_warehouse')->insert([
            'item_id' => $item->id,
            'warehouse_id' => $warehouse->id,
            'stock' => 20,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    $inventory = $db->table('inventories')
        ->where('description', 'TEST Inventario ' . str_pad($i, 2, '0', STR_PAD_LEFT))
        ->first();
    if (!$inventory) {
        $db->table('inventories')->insert([
            'type' => 1,
            'description' => 'TEST Inventario ' . str_pad($i, 2, '0', STR_PAD_LEFT),
            'detail' => 'Carga de prueba',
            'item_id' => $item->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 20,
            'comments' => 'Datos de prueba; no representa una operación real.',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
}

$documents = [];
for ($i = 1; $i <= 20; $i++) {
    $isInvoice = $i % 2 === 0;
    $documentType = $isInvoice ? '01' : '03';
    $group = $isInvoice ? $groupInvoice : $groupReceipt;
    $documentSeries = $series[$documentType]->number;
    $documentNumber = 900000 + $i;
    $document = $db->table('documents')
        ->where('series', $documentSeries)
        ->where('number', $documentNumber)
        ->first();

    if (!$document) {
        $customer = $customers[$i - 1];
        $item = $items[$i - 1];
        $value = 100 + $i;
        $tax = round($value * 0.18, 2);
        $total = $value + $tax;
        $documentId = $db->table('documents')->insertGetId([
            'user_id' => $user->id,
            'external_id' => (string) Str::uuid(),
            'establishment_id' => $establishment->id,
            'establishment' => json_encode($establishment),
            'soap_type_id' => $soap->id,
            'state_type_id' => $state->id,
            'ubl_version' => '2.1',
            'group_id' => $group->id,
            'document_type_id' => $documentType,
            'series' => $documentSeries,
            'number' => $documentNumber,
            'date_of_issue' => date('Y-m-d', strtotime("-{$i} days")),
            'time_of_issue' => $time,
            'customer_id' => $customer->id,
            'customer' => json_encode($customer),
            'currency_type_id' => $currency->id,
            'exchange_rate_sale' => 1,
            'total_taxed' => $value,
            'total_igv' => $tax,
            'total_taxes' => $tax,
            'total_value' => $value,
            'subtotal' => $value,
            'total' => $total,
            'has_xml' => 0,
            'has_pdf' => 0,
            'has_cdr' => 0,
            'send_server' => 0,
            'success_shipping_status' => 0,
            'success_sunat_shipping_status' => 0,
            'total_canceled' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $db->table('document_items')->insert([
            'document_id' => $documentId,
            'item_id' => $item->id,
            'item' => json_encode($item),
            'quantity' => 1,
            'unit_value' => $value,
            'affectation_igv_type_id' => $affectation->id,
            'total_base_igv' => $value,
            'percentage_igv' => 18,
            'total_igv' => $tax,
            'total_taxes' => $tax,
            'price_type_id' => $priceType->id,
            'unit_price' => $total,
            'total_value' => $value,
            'total' => $total,
            'warehouse_id' => $warehouses[$i - 1]->id,
        ]);
        $db->table('document_payments')->insert([
            'document_id' => $documentId,
            'date_of_payment' => date('Y-m-d', strtotime("-{$i} days")),
            'payment_method_type_id' => $paymentMethod->id,
            'payment' => $total,
            'change' => 0,
        ]);
        $document = $db->table('documents')->find($documentId);
    }
    $documents[] = $document;
}

for ($i = 1; $i <= 20; $i++) {
    $purchaseNumber = 800000 + $i;
    $purchase = $db->table('purchases')
        ->where('series', 'C001')
        ->where('number', $purchaseNumber)
        ->first();
    if (!$purchase) {
        $supplier = $suppliers[$i - 1];
        $item = $items[$i - 1];
        $value = 100 + $i;
        $tax = round($value * 0.18, 2);
        $total = $value + $tax;
        $purchaseId = $db->table('purchases')->insertGetId([
            'user_id' => $user->id,
            'external_id' => (string) Str::uuid(),
            'establishment_id' => $establishment->id,
            'soap_type_id' => $soap->id,
            'state_type_id' => $state->id,
            'group_id' => $groupInvoice->id,
            'document_type_id' => '01',
            'series' => 'C001',
            'number' => $purchaseNumber,
            'date_of_issue' => date('Y-m-d', strtotime("-{$i} days")),
            'time_of_issue' => $time,
            'supplier_id' => $supplier->id,
            'supplier' => json_encode($supplier),
            'currency_type_id' => $currency->id,
            'exchange_rate_sale' => 1,
            'total_taxed' => $value,
            'total_igv' => $tax,
            'total_taxes' => $tax,
            'total_value' => $value,
            'total' => $total,
            'total_canceled' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $db->table('purchase_items')->insert([
            'purchase_id' => $purchaseId,
            'item_id' => $item->id,
            'item' => json_encode($item),
            'quantity' => 1,
            'unit_value' => $value,
            'affectation_igv_type_id' => $affectation->id,
            'total_base_igv' => $value,
            'percentage_igv' => 18,
            'total_igv' => $tax,
            'total_taxes' => $tax,
            'price_type_id' => $priceType->id,
            'unit_price' => $total,
            'total_value' => $value,
            'total' => $total,
            'warehouse_id' => $warehouses[$i - 1]->id,
        ]);
    }
}

for ($i = 1; $i <= 20; $i++) {
    $expenseNumber = 'TEST-' . str_pad($i, 2, '0', STR_PAD_LEFT);
    $expense = $db->table('expenses')->where('number', $expenseNumber)->first();
    if (!$expense) {
        $supplier = $suppliers[$i - 1];
        $total = 50 + $i;
        $expenseId = $db->table('expenses')->insertGetId([
            'user_id' => $user->id,
            'expense_type_id' => $expenseType->id,
            'establishment_id' => $establishment->id,
            'supplier_id' => $supplier->id,
            'currency_type_id' => $currency->id,
            'external_id' => (string) Str::uuid(),
            'number' => $expenseNumber,
            'date_of_issue' => date('Y-m-d', strtotime("-{$i} days")),
            'time_of_issue' => $time,
            'supplier' => json_encode($supplier),
            'exchange_rate_sale' => 1,
            'total' => $total,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $db->table('expense_items')->insert([
            'expense_id' => $expenseId,
            'description' => "TEST Gasto {$i}",
            'total' => $total,
        ]);
    }
}

for ($i = 1; $i <= 20; $i++) {
    $reference = 'TEST-CAJA-' . str_pad($i, 2, '0', STR_PAD_LEFT);
    $cash = $db->table('cash')->where('reference_number', $reference)->first();
    if (!$cash) {
        $cashId = $db->table('cash')->insertGetId([
            'user_id' => $user->id,
            'date_opening' => date('Y-m-d', strtotime("-{$i} days")),
            'time_opening' => $time,
            'beginning_balance' => 100,
            'final_balance' => 100,
            'income' => 0,
            'state' => 0,
            'reference_number' => $reference,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $document = $documents[$i - 1];
        $db->table('cash_documents')->insert([
            'cash_id' => $cashId,
            'document_id' => $document->id,
        ]);
    }
}

echo "Carga completada en {$database}.\n";
echo "Prefijo de prueba: TEST-; no se eliminaron registros existentes.\n";
echo "Registros objetivo: 20 clientes, 20 proveedores, 20 productos, almacenes, inventarios, compras, documentos, gastos y cajas.\n";
