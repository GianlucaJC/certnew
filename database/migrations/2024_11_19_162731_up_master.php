<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
		Schema::table('tbl_master', function ($table) {
            $table->string('id_clone_from',200)->after('id_doc')->nullable();
		});  
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
