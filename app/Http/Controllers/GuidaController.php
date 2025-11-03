<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class GuidaController extends Controller
{
    /**
     * Mostra la pagina della guida per l'operatore.
     */
    public function index()
    {
        return view('all_views.guida_operatore');
    }
}