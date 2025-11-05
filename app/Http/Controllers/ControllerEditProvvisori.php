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
use App\Models\tbl_master;
use App\Models\impegnolotti;
use App\Models\cert_provvisori;

use DB;




class ControllerEditProvvisori extends Controller
{
    public function __construct(){
    }


	public static function getClient($scope="docs")
	{
		$client = new Client();
		$client->setApplicationName('certOAuth');
		$client->setAuthConfig('credentials.json');

		/*
		if ($scope=="docs")
			$client->setScopes([\Google_Service_Docs::DOCUMENTS]);
		if ($scope=="list_drive")
			$client->setScopes([\Google_Service_Drive::DRIVE]);
		
		//$client->setScopes([\Google_Service_Drive::DRIVE_FILE]);
		*/
        
		$client->setScopes("https://www.googleapis.com/auth/documents");
		//equivalente: $client->setScopes([\Google_Service_Docs::DOCUMENTS]);

        //presente in credentials.json
		$client->setConfig('subject', 'account-service-cert@certoauth-439509.iam.gserviceaccount.com');
        $client->setIncludeGrantedScopes(true);

		$client->setAccessType('offline');
	
		return $client;
	}

	public function edit_provvisorio($id,$id_provv) {
		$all_tag=$this->all_tag($id_provv,"");

		$info_provv=cert_provvisori::from('cert_provvisori')
		->select('id_doc','lotto','codice','codice_associato_master','stato','created_at','updated_at', 'real_name')
		->where('id_doc','=',$id_provv)
		->first(); 

		$master_doc_id = null;
		if ($info_provv) {
			$master_real_name = str_replace('.doc', '', $info_provv->real_name);
			$master = tbl_master::where('real_name', $master_real_name)->first();
			if ($master) {
				$master_doc_id = $master->id_doc;
			}
		}

		return view('all_views/provvisori/edit_provvisorio',compact('info_provv','id_provv','all_tag', 'master_doc_id'));
    } 

	function load_clone(Request $request) {
		
		$doc_id=$request->input('doc_id');		
		 

		$info=$this->open_doc($doc_id);
		$str_all='
			<input name="_token" type="hidden" value="'.csrf_token().'" id="token_csrf">
      		<meta name="csrf-token" content="'.csrf_token().'">
			<input type="hidden" name="url" id="url" value="'.url('/').'">';

		$str_all.=$info['str_all'];

		//alcuni caratteri vengono trasformati in simboli
		//il simbolo di copyright in un cuore!
		$str_all=str_replace("Symbol","Belleza",$str_all); 


		$tags=$info['all_tag'];
		$all_tag=$tags['tags'];
		$tag_O="&lt;";
		$tag_C="&gt;";

        //$tag_O="[";
		//$tag_C="]";

		$tags_compilabili = 0;
		foreach ($all_tag as $indice=>$tag_ref ) {
			// Pulisce il tag da tutti i possibili delimitatori per poterlo analizzare
			$tag = preg_replace('/(?:&lt;|\$|\[\[)(.*?)(?:&gt;|\$|\]\])/', '$1', $tag_ref);

			// Controlla se il tag è uno di quelli riservati per la firma.
			// Se lo è, salta la sostituzione con un campo di input.
			// Il tag rimarrà evidenziato in giallo ma non sarà editabile.
			if (in_array($tag, ['firma', 'firma_d'])) {
				continue; // Salta al prossimo tag, non è compilabile manualmente
			}
			$tags_compilabili++;
			$tag_sost = $this->render_input($tag, $indice);
			$str_all = str_replace($tag_ref, $tag_sost, $str_all);
		}	
		//il relativo file JS che gestiste il provvisorio è in dist/js/add_script.js che viene iniettato tramite iframe
		$str_all.="<hr>";

		if ($tags_compilabili > 0) {
			$str_all.="
				<center>
					<button type='button' class='btn btn-primary' onclick=\"save_all('$doc_id')\"  name='btn_save_cont' id='btn_save_cont'>Salva dati</button> 
				</center>	
			";
		} else {
			$str_all.="
				<div class=\"alert alert-success mt-3\" role=\"alert\">
					<h4 class=\"alert-heading\">Compilazione Completata!</h4>
					<p>Tutti i tag del documento sono stati compilati. Ora puoi passare questo certificato allo stato \"Pronto\" dalla pagina \"Elenco Provvisori\".</p>
				</div>
			";
		}

		// Aggiungo la modale di conferma per il cambio di stato
		$str_all.='
			<div class="modal fade" id="confirmStateChangeModal" tabindex="-1" aria-labelledby="confirmStateChangeModalLabel" aria-hidden="true">
			  <div class="modal-dialog">
				<div class="modal-content">
				  <div class="modal-header">
					<h5 class="modal-title" id="confirmStateChangeModalLabel">Conferma Cambio Stato</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				  </div>
				  <div class="modal-body">
					Sei sicuro di voler passare il documento allo stato "pronto per trasformazione definitivo"? L\'operazione non è reversibile da questa interfaccia.
				  </div>
				  <div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
					<button type="button" class="btn btn-success" id="confirmStateChangeBtn">Conferma</button>
				  </div>
				</div>
			  </div>
			</div>';

		$info['content']=$str_all;
		return $info;		
	}	

	function render_input($ref_tag,$sca) {
		// Pulisce il tag dai suoi delimitatori per ottenere il nome puro
		$tag_name = preg_replace('/^(&lt;|\[\[|\[|\$)|(&gt;|\]\]|\]|\$)$/', '', $ref_tag);
		$place = $tag_name; // Placeholder di default
		$html = "";
	
		// Gestione dei tag speciali conosciuti
		if ($tag_name == "pdate" || $tag_name == "exp" || $tag_name == "fcont") {
			if ($tag_name == "pdate") $place = "Data produzione";
			if ($tag_name == "exp") $place = "Data scadenza";
			if ($tag_name == "fcont") $place = "Data approvazione";
			$html = "<input type='date' class='dati' data-id_ref='$tag_name' id='tg$sca' data-id='tg$sca' data-tag='$tag_name' style='width:150px' placeholder='$place'>";
		} elseif ($tag_name == "id" || $tag_name == "nid") {
			$lbl = ($tag_name == "id") ? "Idoneo" : "Non idoneo";
			$html = "<div class='form-group'>
						<label for='tg$sca'>$lbl</label>
						<select class='form-select dati' data-id_ref='$tag_name' id='tg$sca' data-id='tg$sca' data-tag='$tag_name'>
							<option value=''>-- Seleziona --</option>
							<option value='☑'>Idoneo (con spunta)</option>
							<option value='☐'>Non Idoneo (senza spunta)</option>
						</select>
					</div>";
		} else {
			// Blocco generico per tutti gli altri tag (conosciuti come 'lt' o custom)
			if ($tag_name == "lt") $place = "Lotto"; // Placeholder specifico per 'lt'
			$html = "<input type='text' class='dati' data-id_ref='$tag_name' id='tg$sca' data-id='tg$sca' data-tag='$tag_name' style='width:150px' placeholder='$place'>";
		}
		return $html;
	}

	function view_tag(Request $request) {
		$doc_id=$request->input('doc_id');
		$tag=$request->input('tag');

		
		$info=$this->open_doc($doc_id);
		$str_all=$info['str_all'];
		
		$content=$str_all;
		//$tags=$this->get_string_between($content,"<",">");
		$tag_O="&lt;";
		$tag_C="&gt;";

		//$tag_O="[";
		//$tag_C="]";

		$tag_ref=$tag_O.$tag.$tag_C;
		$tag_sost="<span style='background-color:yellow'>".$tag_ref."</span>";
		$str_all=str_replace($tag_ref,$tag_sost,$str_all);
		$info['content']=$str_all;
		return $info;
	}

	function save_dati(Request $request) {
		$posts=request()->post();
		$doc_id=$request->input('doc_id');
		$this->edit_doc($doc_id,$posts);

		// --- INIZIO CALCOLO PERCENTUALE COMPLETAMENTO ---

		// 1. Conto i tag totali originali (quelli associati al master)
		$provvisorio = cert_provvisori::where('id_doc', $doc_id)->first(); // Lo uso per l'update finale

		if ($provvisorio) {
			// Eseguo una join per trovare l'id_doc del master in modo univoco
			$master = DB::table('cert_provvisori as p')
				->join('tbl_master as m', DB::raw("REPLACE(p.real_name, '.doc', '')"), '=', 'm.real_name')
				->where('p.id_doc', $doc_id)
				->select('m.id_doc')
				->first();

			if ($master) {
				$info_master_doc = $this->open_doc($master->id_doc);
				$tags_info_totali = $this->all_tag($master->id_doc, $info_master_doc['str_all']);
				$tags_totali = $tags_info_totali['num_tag'];

				// 2. Conto i tag rimasti nel documento provvisorio appena modificato
				$tags_info_correnti = $this->all_tag($doc_id, ""); // Riapro il documento per contare i tag
				$tags_rimasti = $tags_info_correnti['num_tag'];

				// 3. Calcolo e aggiorno la percentuale
				$perc_complete = ($tags_totali > 0) ? round((($tags_totali - $tags_rimasti) / $tags_totali) * 100) : 0;
				
				$provvisorio->update(['perc_complete' => $perc_complete]);
			}
		}
		// --- FINE CALCOLO PERCENTUALE COMPLETAMENTO ---
	}

	function save_to_ready(Request $request) {
		$doc_id=$request->input('doc_id');
        $up=cert_provvisori::where('id_doc','=',$doc_id)->update(['stato' =>1]);
        $esito['header']="OK";
        $esito['up']=$up;
        echo json_encode($esito);		
	}



	function save_tag_edit(Request $request) {
		$posts=request()->post();
		$doc_id=$request->input('doc_id');
		$tag=$request->input('tag');
		$this->edit_doc($doc_id,$posts);
	}

	function get_string_between ($str,$from,$to) {
		$string = substr($str, strpos($str, $from) + strlen($from));
		if (strstr ($string,$to,TRUE) != FALSE) {
			$string = strstr ($string,$to,TRUE);
		}
		return $string;
	}

	public function open_doc($fileId) {
        $client = new \Google_Client();
				
        $client->setClientId(env('GOOGLE_DRIVE_CLIENT_ID'));
        $client->setClientSecret(env('GOOGLE_DRIVE_CLIENT_SECRET'));
        $client->refreshToken(env('GOOGLE_DRIVE_REFRESH_TOKEN'));

		

        $service = new \Google_Service_Drive($client);
		
		

		$response = $service->files->export($fileId, 'text/html', array('alt' => 'media' ));
		$str_all = $response->getBody()->getContents();
		$all_tag=$this->all_tag($fileId,$str_all);


		$resp=array();
		$resp['str_all']=$str_all;
		$resp['all_tag']=$all_tag;
		return $resp;

	}

	public function all_tag($fileId,$str_all="") {
		$tags=array();
		if (strlen($str_all)==0) {$info=$this->open_doc($fileId);$str_all=$info['str_all'];}
		
		$content=$str_all;

		$tag_O="&lt;";
		$tag_C="&gt;";

		// Regex unificata per trovare tutti i tipi di tag: [[tag]], [tag], &lt;tag&gt;, $tag$
		// Il gruppo di cattura interno ([a-zA-Z0-9_]+) estrae solo il nome del tag.
		$pattern = '/(?:\[\[([a-zA-Z0-9_]+)\]\]|\[([a-zA-Z0-9_]+)\]|&lt;([a-zA-Z0-9_]+)&gt;|\$([a-zA-Z0-9_]+)\$)/';
		preg_match_all($pattern, $content, $matches);

		$all_found_tags = $matches[0]; // Tutti i tag trovati con i delimitatori (es. [[lt]], $firma$)
		$tags_compilabili = [];

		// Filtra i tag per escludere quelli non compilabili manualmente (come firma e firma_d)
		foreach ($all_found_tags as $tag_ref) {
			// Pulisce il tag dai delimitatori per analizzarne il contenuto
			$tag_content = preg_replace('/(?:&lt;|\$|\[\[|\]\]|&gt;)/', '', $tag_ref);
			if (!in_array($tag_content, ['firma', 'firma_d'])) {
				$tags_compilabili[] = $tag_ref;
			}
		}

		// num_tag ora conta solo i tag che non sono 'firma' o 'firma_d'.
		$num_tag = count($tags_compilabili);

		$info=array();
		$info['num_tag']=$num_tag;
		$info['tags']=$tags_compilabili; // Restituisce solo i tag compilabili
		$info['content']=$str_all;
		return $info; // num_tag, tags, content
	}


    public static function set_fill($documentId,$info_lotto) {
		// Funzione utilizzata per il completamento dei tag automatici (es: lotto e scadenza)
		$client = ControllerEditprovvisori::getClient("docs");
		$service = new \Google_Service_Docs($client);
		//open doc and edit
		$doc = $service->documents->get($documentId);
		
		$requests = [];
		foreach ($info_lotto as $tag=>$modifiedText ) {
			// Per ogni dato ricevuto (es. 'lt'), creo un array di tutti i possibili
			// formati di tag da cercare nel documento (es. [[lt]], <lt>, $lt$, etc.).
			if (empty($modifiedText)) continue; // Salta se il valore è vuoto
			$tag_formats_to_replace = [
				"<$tag>",
				"&lt;$tag&gt;",
				"$$tag$",
				"[[$tag]]",
				"[$tag]" // Aggiungo anche il formato con parentesi singole per sicurezza				
			];

			foreach ($tag_formats_to_replace as $tag_to_find) {
				$requests[] = new \Google_Service_Docs_Request([
					'replaceAllText' => [
						'replaceText' => $modifiedText,
						'containsText' => [
							'text' => $tag_to_find,
							'matchCase' => true,
						],
					],
				]);
			}
        }

		if (count($requests) > 0) {
			$batchUpdateRequest = new \Google_Service_Docs_BatchUpdateDocumentRequest(['requests' => $requests]);
			$response = $service->documents->batchUpdate($documentId, $batchUpdateRequest);
		}
        return true;
    }

	public function edit_doc($documentId,$posts) {
		$client = $this->getClient("docs");
		$service = new \Google_Service_Docs($client);

		//open doc and edit
		$doc = $service->documents->get($documentId);
		foreach ($posts as $tag=>$modifiedText ) {
			if ($tag == "_token" || $tag == "doc_id") continue;

			// Per ogni dato ricevuto (es. 'fcont'), creo un array di tutti i possibili
			// formati di tag da cercare nel documento (es. [[fcont]], <fcont>, $fcont$, etc.).
			$tag_formats_to_replace = [
				"<$tag>",
				"&lt;$tag&gt;",
				"$$tag$",
				"[[$tag]]",
				"[$tag]" // Aggiungo anche il formato con parentesi singole per sicurezza
			];

			$requests = [];

			foreach ($tag_formats_to_replace as $tag_to_find) {
				$requests[] = new \Google_Service_Docs_Request([
					'replaceAllText' => [
						'replaceText' => $modifiedText,
						'containsText' => [
							'text' => $tag_to_find,
							'matchCase' => true, // Mantengo la sensibilità per evitare sostituzioni errate
						],
					],
				]);
			}

			if (count($requests) > 0) {
				$batchUpdateRequest = new \Google_Service_Docs_BatchUpdateDocumentRequest(['requests' => $requests]);
				$response = $service->documents->batchUpdate($documentId, $batchUpdateRequest);
			}
		}
		$resp=array();
		$resp['header']="OK";
		echo json_encode($resp);
	}
}