<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('ruc')->nullable();
            $table->string('razonsocial')->nullable();
            $table->string('ubigeo')->nullable();
            $table->string('direccion')->nullable();
            $table->string('urbanizacion')->nullable();
            $table->string('nombrecomercial')->nullable();
            $table->string('logo')->nullable();
            $table->string('soluser')->nullable();
            $table->string('solpass')->nullable();;
            $table->text('certificado')->nullable();
            $table->string('certificate_path')->nullable();
            $table->string('cliente_id')->nullable();
            $table->string('cliente_secret')->nullable();
            $table->boolean('production')->default(0);//si no
            $table->boolean('state')->default(1);
            $table->string('ublversion')->nullable();
            $table->double('detraccion', 10, 4)->nullable();
            //$table->foreignId('currency_id')->constrained();//moneda por defecto para los comprobantes
            $table->unsignedBigInteger('currency_id')->nullable();
            $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('cascade');




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
        Schema::dropIfExists('companies');
    }
};
