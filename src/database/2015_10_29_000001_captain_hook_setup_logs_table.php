<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CaptainHookSetupLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('webhook_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('webhook_id')->unsigned()->nullable();
            $table->foreign('webhook_id')->references('id')->on('webhooks')->onDelete('set null');
            $table->string('url');
            $table->string('payload_format')->nullable();
            $table->text('payload');
            $table->integer('status');
            $table->text('response');
            $table->string('response_format')->nullable();
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
        Schema::drop('webhook_logs');
    }
}
