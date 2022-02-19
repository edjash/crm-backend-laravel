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
                ->constrained('contacts')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->foreignId('company_id')
                ->nullable()
                ->constrained('companies')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->string('street', 255)->nullable();
            $table->string('town', 255)->nullable();
            $table->string('county', 255)->nullable();
            $table->string('postcode', 255)->nullable();
            $table->string('country_code', 3)->nullable();
            $table->string('country_name', 255)->nullable();
            $table->text('full_address')->nullable();
            $table->string('label', 255)->nullable();
            $table->tinyInteger('display_index')->default(0);
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
