<?php
namespace App\Http\Controllers;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Google\Client;
use Google\Service\Drive;
use Yaza\LaravelGoogleDriveStorage\Gdrive;
use App\Models\tbl_master;
use App\Models\impegnolotti;
use App\Models\cert_provvisori;


use DB;

class ControllerDefinitivi extends Controller
{
    public function __construct(){
    }


	public function elenco_definitivi_idonei() {
       
        $elenco_definitivi_idonei=cert_provvisori::from('cert_provvisori as p')
        ->select('p.id','p.id_doc','p.lotto','p.codice','p.codice_associato_master','p.stato','p.created_at','p.updated_at')
        ->where('p.stato','=',2)
        ->get(); 
		return view('all_views/definitivi/elenco_definitivi_idonei',compact('elenco_definitivi_idonei'));
    }

	public function elenco_definitivi_non_idonei() {
       
        $elenco_definitivi_non_idonei=cert_provvisori::from('cert_provvisori as p')
        ->select('p.id','p.id_doc','p.lotto','p.codice','p.codice_associato_master','p.stato','p.created_at','p.updated_at')
        ->where('p.stato','=',3)
        ->get(); 
		return view('all_views/definitivi/elenco_definitivi_non_idonei',compact('elenco_definitivi_non_idonei'));
    }


}	