<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCapturedQueriesTable extends Migration
{
    public function up()
    {
        Schema::create('captured_queries', function (Blueprint $table) {
            $table->id();
            $table->text('sql');
            $table->json('bindings')->nullable();
            $table->float('time');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('captured_queries');
    }
}
