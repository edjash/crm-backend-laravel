<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSocialMediaUrlsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('socialmedia_urls', function (Blueprint $table) {
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
            $table->string('ident', 255)->nullable();
            $table->string('url', 255)->nullable();
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
        Schema::dropIfExists('socialmedia_urls');
    }
}
