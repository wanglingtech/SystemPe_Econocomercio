<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TenantCarrierGuideItemsTable extends Migration
{
    public function up()
    {
        Schema::create('carrier_guide_items', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('carrier_guide_id');
            $table->unsignedInteger('item_id');
            $table->json('item');
            $table->decimal('quantity', 10, 2);
            $table->string('unit_type_id');
            $table->string('description');
            $table->timestamps();

            $table->foreign('carrier_guide_id')->references('id')->on('carrier_guides');
            $table->foreign('item_id')->references('id')->on('items');
            $table->foreign('unit_type_id')->references('id')->on('cat_unit_types');
        });
    }

    public function down()
    {
        Schema::dropIfExists('carrier_guide_items');
    }
}