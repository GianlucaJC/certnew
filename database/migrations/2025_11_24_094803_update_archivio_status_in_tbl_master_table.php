<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Step 1: Add the new 'archivio' column
        Schema::table('tbl_master', function (Blueprint $table) {
            $table->string('archivio', 50)->default('attivo')->after('obsoleti')->comment('Stato di archiviazione del master: attivo, obsoleto, confermato, escluso, etc.');
            $table->index('archivio');
        });

        // Step 2: Migrate existing data to the new column
        // Mark as 'obsoleto' masters that were previously archived (obsoleti=1) or soft-deleted (dele=1)
        DB::table('tbl_master')->where('obsoleti', 1)->orWhere('dele', 1)->update(['archivio' => 'obsoleto']);
        
        // Mark as 'escluso' masters that were marked for exclusion from local sync (obsoleti=3)
        DB::table('tbl_master')->where('obsoleti', 3)->update(['archivio' => 'escluso']);

        // Tutti gli altri (obsoleti=0 e dele=0) avranno già il default 'attivo', quindi non serve un'altra query.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tbl_master', function (Blueprint $table) {
            $table->dropColumn('archivio');
        });
    }
};
