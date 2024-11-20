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
            $table->integer('obsoleti')->after('real_name');
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
