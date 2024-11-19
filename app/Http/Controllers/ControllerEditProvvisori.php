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

        $info_provv=cert_provvisori::from('cert_provvisori as p')
        ->select('p.id_doc','p.lotto','p.codice','p.codice_associato_master','p.stato','p.created_at','p.updated_at')
        ->where('id','=',$id)
        ->get(); 

		return view('all_views/provvisori/edit_provvisorio',compact('info_provv','id_provv','all_tag'));
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
		//$tag_O="&lt;";
		//$tag_C="&gt;";

        $tag_O="[";
		$tag_C="]";

		$entr=0;
		foreach ($all_tag as $indice=>$tag_ref ) {
			$entr++;
			$tag=str_replace($tag_O,"",$tag_ref);$tag=str_replace($tag_C,"",$tag);
			$tag_sost="<input type='text' class='dati' data-id_ref='$tag' style='width:80px'>";
			$str_all=str_replace($tag_ref,$tag_sost,$str_all);
		}	
		//il relativo file JS che gestiste il provvisorio è in dist/js/add_script.js che viene iniettato tramite iframe
		$str_all.="<hr>";
		
		if ($entr>0) {
			$str_all.="
				<center>
					<button type='button' onclick=\"save_all('$doc_id')\"  name='btn_save_cont' id='btn_save_cont'>Salva dati</button> 
				</center>	
			";
		} else {
			$str_all.="
				<center>
					<button type='button' onclick=\"save_to_ready('$doc_id')\"  name='btn_ready' id='btn_ready'>Passa documento in 'pronto per trasformazione definitivo'</button> 
				</center>	
			";
		}


		$info['content']=$str_all;
		return $info;		
	}	

	function view_tag(Request $request) {
		$doc_id=$request->input('doc_id');
		$tag=$request->input('tag');

		
		$info=$this->open_doc($doc_id);
		$str_all=$info['str_all'];
		
		$content=$str_all;
		//$tags=$this->get_string_between($content,"<",">");
		//$tag_O="&lt;";
		//$tag_C="&gt;";

		$tag_O="[";
		$tag_C="]";

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
		//$tags=$this->get_string_between($content,"<",">");
		//$tag_O="&lt;";
		//$tag_C="&gt;";

		$tag_O="[";
		$tag_C="]";

        $sc1 = substr_count($content, $tag_O);
		$sc2 = substr_count($content, $tag_C);

		$num_tag=$sc1;
		$start=0;
		for ($s=0;$s<=$num_tag-1;$s++) {
			$content_tag = $this->get_string_between($content, $tag_O , $tag_C);
			$rep=$tag_O.$content_tag.$tag_C;
			//ogni iterazione deve comportare la sostituzione di un tag con qualcosa (se ciò non avviene la function get_string_between riceve sempre stessi parametri)
			$content=str_replace($rep,"",$content);
			$tags[]=trim(strip_tags($rep));
		}	
		$info=array();
		$info['num_tag']=$num_tag;
		$info['tags']=$tags;
		$info['content']=$str_all;
		return $info;
	}


    public static function set_fill($documentId,$info_lotto) {
		$client = ControllerEditprovvisori::getClient("docs");
		$service = new \Google_Service_Docs($client);
		//open doc and edit
		$doc = $service->documents->get($documentId);
		foreach ($info_lotto as $tag=>$modifiedText ) {
            
            $tag="[$tag]";
            

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
				$tag="[$tag]";
	
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
		$resp=array();
		$resp['header']="OK";
		echo json_encode($resp);
	
	}
    

}	