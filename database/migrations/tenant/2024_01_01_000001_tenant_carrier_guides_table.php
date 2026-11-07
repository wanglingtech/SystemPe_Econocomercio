<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TenantCarrierGuidesTable extends Migration
{
    public function up()
    {
        Schema::create('carrier_guides', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->uuid('external_id');
            $table->unsignedInteger('establishment_id');
            $table->json('establishment');
            $table->char('soap_type_id', 2);
            $table->char('state_type_id', 2);
            $table->string('ubl_version')->default('2.1');
            $table->string('document_type_id')->default('31'); // Guía transportista
            $table->char('series', 4);
            $table->integer('number');
            $table->date('date_of_issue');
            $table->time('time_of_issue');
            // Remitente (quien envía)
            $table->unsignedInteger('sender_id');
            $table->json('sender');
            // Destinatario (quien recibe)
            $table->unsignedInteger('receiver_id');
            $table->json('receiver');
            // Datos del transportista
            $table->json('carrier'); // datos empresa transportista
            $table->json('driver')->nullable(); // conductor
            $table->string('license_plate')->nullable(); // placa vehículo
            $table->json('secondary_license_plates')->nullable();
            // Datos del traslado
            $table->string('transport_mode_type_id'); // 01=privado, 02=público
            $table->string('transfer_reason_type_id');
            $table->string('transfer_reason_description')->nullable();
            $table->date('date_of_shipping');
            $table->boolean('transshipment_indicator')->default(false);
            $table->string('port_code')->nullable();
            // Peso y bultos
            $table->string('unit_type_id');
            $table->decimal('total_weight', 10, 2);
            $table->integer('packages_number')->nullable();
            $table->integer('container_number')->nullable();
            // Origen y destino
            $table->json('origin');
            $table->json('delivery');
            // Referencia a guía de remisión
            $table->unsignedInteger('dispatch_id')->nullable();
            $table->string('observations')->nullable();
            $table->json('legends')->nullable();
            // Archivos
            $table->string('filename')->nullable();
            $table->string('hash')->nullable();
            $table->boolean('has_xml')->default(false);
            $table->boolean('has_pdf')->default(false);
            $table->boolean('has_cdr')->default(false);
            $table->json('soap_shipping_response')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('establishment_id')->references('id')->on('establishments');
            $table->foreign('soap_type_id')->references('id')->on('soap_types');
            $table->foreign('state_type_id')->references('id')->on('state_types');
            $table->foreign('sender_id')->references('id')->on('persons');
            $table->foreign('receiver_id')->references('id')->on('persons');
            $table->foreign('unit_type_id')->references('id')->on('cat_unit_types');
            $table->foreign('transport_mode_type_id')->references('id')->on('cat_transport_mode_types');
            $table->foreign('transfer_reason_type_id')->references('id')->on('cat_transfer_reason_types');
        });
    }

    public function down()
    {
        Schema::dropIfExists('carrier_guides');
    }
}