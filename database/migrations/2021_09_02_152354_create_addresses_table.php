<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAddressesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_id')
                ->nullable()
                ->constrained()
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->foreignId('company_id')
                ->nullable()
                ->constrained('companies')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->string('type', 10);
            $table->string('line1', 255);
            $table->string('line2', 255);
            $table->string('town', 255);
            $table->string('county', 255);
            $table->string('country_code', 255);
            $table->string('country_name', 255);
            $table->string('postcode', 255);
            $table->text('full_address');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('addresses');
    }
}
