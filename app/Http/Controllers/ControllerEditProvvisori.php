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

		$entr=0;
		foreach ($all_tag as $indice=>$tag_ref ) {
			$entr++;
			// Pulisce il tag dai delimitatori per poterlo analizzare
			$tag = str_replace(['&lt;', '&gt;', '$'], '', $tag_ref);

			// Controlla se il tag è uno di quelli riservati per la firma.
			// Se lo è, salta la sostituzione con un campo di input.
			// Il tag rimarrà evidenziato in giallo ma non sarà editabile.
			if (in_array($tag, ['firma', 'firma_d'])) {
				continue; // Salta al prossimo tag
			}
			$tag_sost = $this->render_input($tag_ref, $tag, $indice);
			$str_all = str_replace($tag_ref, $tag_sost, $str_all);
		}	
		//il relativo file JS che gestiste il provvisorio è in dist/js/add_script.js che viene iniettato tramite iframe
		$str_all.="<hr>";
		
		if ($entr>0) {
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

	function render_input($tag,$ref_tag,$sca) {
		$place = $tag; // Default placeholder
		$html = "";

		if ($ref_tag == "pdate" || $ref_tag == "exp" || $ref_tag == "fcont") {
			if ($ref_tag == "pdate") $place = "Data produzione";
			if ($ref_tag == "exp") $place = "Data scadenza";
			if ($ref_tag == "fcont") $place = "Data approvazione";
			$html = "<input type='date' class='dati' data-id_ref='$ref_tag' id='tg$sca' data-id='tg$sca' data-tag='$ref_tag' style='width:150px' placeholder='$place'>";
		} elseif ($ref_tag == "id" || $ref_tag == "nid") {
			$lbl = ($ref_tag == "id") ? "Idoneo" : "Non idoneo";
			$html = "<div class='form-group'>
						<label for='tg$sca'>$lbl</label>
						<select class='form-select dati' data-id_ref='$ref_tag' id='tg$sca' data-id='tg$sca' data-tag='$ref_tag'>
							<option value=''>-- Seleziona --</option>
							<option value='☑'>Idoneo (con spunta)</option>
							<option value='☐'>Non Idoneo (senza spunta)</option>
						</select>
					</div>";
		} else {
			// Questo blocco gestisce 'lt' e qualsiasi altro tag generico come input di testo.
			if ($ref_tag == "lt") $place = "Lotto"; // Placeholder specifico per 'lt'
			$html = "<input type='text' class='dati' data-id_ref='$ref_tag' id='tg$sca' data-id='tg$sca' data-tag='$ref_tag' style='width:80px' placeholder='$place'>";
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

		// Regex unificata per trovare tutti i tipi di tag in una sola passata.
		// Supporta sia il formato classico (&lt;tag&gt;) che quello robusto ($tag$).
		// Questa logica è ora allineata a quella usata in edit_provvisorio.blade.php
		$pattern = '/(?:&lt;[a-zA-Z][^&]*?&gt;|\$[a-zA-Z_0-9]+\$)/';
		preg_match_all($pattern, $content, $matches);

		// $matches[0] contiene i tag completi (es. &lt;fcont&gt;)
		// $matches[1] contiene solo il contenuto del tag (es. fcont)
		$tags = $matches[0]; // Restituiamo i tag completi come prima
		$num_tag = count($tags);

		$info=array();
		$info['num_tag']=$num_tag;
		$info['tags']=$tags;
		$info['content']=$str_all;
		return $info; // num_tag, tags, content
	}


    public static function set_fill($documentId,$info_lotto) {
		$client = ControllerEditprovvisori::getClient("docs");
		$service = new \Google_Service_Docs($client);
		//open doc and edit
		$doc = $service->documents->get($documentId);
		foreach ($info_lotto as $tag=>$modifiedText ) {
            
            $tag="<$tag>";
            

            $allText[]=$tag;
            // Go through and create search/replace requests
            $requests = $textsAlreadyDone = $forEasyCompare = [];
            foreach ($allText as $currText) {
                if (in_array($currText, $textsAlreadyDone, true)) {
                    // If two identical pieces of text are found only search-and-replace it once - no reason to do it multiple times
                    continue;
                }

                if (preg_match_all("/(.*?)($tag)(.*?)/", $currText, $matches, PREG_SET_ORDER)) {
                    //NOTE: for simple static text searching you could of course just use strpos()
                    // - and then loop on $matches wouldn't be necessary, and str_replace() would be simplified
                    /*
                    $modifiedText = $currText;

                    foreach ($matches as $match) {
                        $modifiedText = str_replace($match[0], $match[1] .$value. $match[3], $modifiedText);
                    }
                    */

                    $forEasyCompare[] = ['old' => $currText, 'new' => $modifiedText];

                    $replaceAllTextRequest = [
                        'replaceAllText' => [
                            'replaceText' => $modifiedText,
                            'containsText' => [
                                'text' => $currText,
                                'matchCase' => true,
                            ],
                        ],
                    ];

                    $requests[] = new \Google_Service_Docs_Request($replaceAllTextRequest);
                }
                $textsAlreadyDone[] = $currText;
            }

            if (count($forEasyCompare)>0) {
                    $batchUpdateRequest = new \Google_Service_Docs_BatchUpdateDocumentRequest(['requests' => $requests]);
                    $response = $service->documents->batchUpdate($documentId, $batchUpdateRequest);
                    //echo "OK";
            }
        }			
        return true;
    }

	public function edit_doc($documentId,$posts) {
		$client = $this->getClient("docs");
		$service = new \Google_Service_Docs($client);

        
		//open doc and edit
		$doc = $service->documents->get($documentId);
		foreach ($posts as $tag=>$modifiedText ) {
				// Ricostruisce il tag per la ricerca.
				// Se il nome del tag contiene '_', è probabile che sia uno dei nuovi tag con '$' (es. $firma_d$).
				// Altrimenti, usa il vecchio formato con le parentesi angolari.
				if (strpos($tag, '_') !== false || in_array($tag, ['firma', 'firma_d'])) {
				// Se il testo modificato proviene da un input (non da un menu a tendina),
				// è probabile che sia un tag con le parentesi angolari.
				// Altrimenti, è uno dei nuovi tag con '$'.
				if (in_array($tag, ['id', 'nid'])) {
					$tag = '$' . $tag . '$';
				} else {
					$tag="<$tag>";
				}
	
					// Collect all pieces of text (see https://developers.google.com/docs/api/concepts/structure to understand the structure)
					$allText = [];
					
					//parsing del testo nel documento
					//non obbligatoriamente necessario ai fini della sostituzione
					
					/*
					foreach ($doc->body->content as $structuralElement) {
						
						if ($structuralElement->paragraph) {
							foreach ($structuralElement->paragraph->elements as $paragraphElement) {
								if ($paragraphElement->textRun) {
									$allText[] = $paragraphElement->textRun->content;
								}
							}
						}
						
						if (1==2) {
							if ($structuralElement->table) {
								foreach ($structuralElement->table as $tb) {
									$tb->TableCell);
									echo "<hr>";
								}
							}
						}
						
					}
					*/
					
					//esempio di sostituzione
				

					$allText[]=$tag;
					// Go through and create search/replace requests
					$requests = $textsAlreadyDone = $forEasyCompare = [];
					foreach ($allText as $currText) {
						if (in_array($currText, $textsAlreadyDone, true)) {
							// If two identical pieces of text are found only search-and-replace it once - no reason to do it multiple times
							continue;
						}

						if (preg_match_all("/(.*?)($tag)(.*?)/", $currText, $matches, PREG_SET_ORDER)) {
							//NOTE: for simple static text searching you could of course just use strpos()
							// - and then loop on $matches wouldn't be necessary, and str_replace() would be simplified
							/*
                            $modifiedText = $currText;
							foreach ($matches as $match) {
								$modifiedText = str_replace($match[0], $match[1] .$value. $match[3], $modifiedText);
							}
                            */

							$forEasyCompare[] = ['old' => $currText, 'new' => $modifiedText];

							$replaceAllTextRequest = [
								'replaceAllText' => [
									'replaceText' => $modifiedText,
									'containsText' => [
										'text' => $currText,
										'matchCase' => true,
									],
								],
							];

							$requests[] = new \Google_Service_Docs_Request($replaceAllTextRequest);
						}
						$textsAlreadyDone[] = $currText;
					}

					// you could dump out $forEasyCompare to see the changes that would be made
					/*
					print_r($allText);
					echo "<hr>";
					print_r($forEasyCompare);
					*/
					if (count($forEasyCompare)>0) {
							$batchUpdateRequest = new \Google_Service_Docs_BatchUpdateDocumentRequest(['requests' => $requests]);
							$response = $service->documents->batchUpdate($documentId, $batchUpdateRequest);
							//echo "OK";
					}
				}			
		}
		$resp=array();
		$resp['header']="OK";
		echo json_encode($resp);
	}
}