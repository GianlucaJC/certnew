<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\tbl_master;
use Illuminate\Support\Facades\Log;

class ControllerMaster extends Controller
{
    // ... altri metodi del controller ...

    /**
     * Cambia lo stato "sistemato" di un master.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function toggle_sistemato(Request $request)
    {
        try {
            $id_doc = $request->input('id_doc');
            if (!$id_doc) {
                return response()->json(['success' => false, 'message' => 'ID documento non fornito.'], 400);
            }

            $master = tbl_master::where('id_doc', $id_doc)->first();

            if ($master) {
                // Inverte il valore booleano di 'sistemato'
                $master->sistemato = !$master->sistemato;
                $master->save();
                return response()->json(['success' => true, 'message' => 'Stato aggiornato con successo.']);
            }

            return response()->json(['success' => false, 'message' => 'Master non trovato.'], 404);
        } catch (\Exception $e) {
            Log::error("Errore in toggle_sistemato: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Errore del server.'], 500);
        }
    }
}