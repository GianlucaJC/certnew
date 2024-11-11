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

use DB;


//ref per concedere a laravel l'accesso al servizio google drive api
//https://gist.github.com/sergomet/f234cc7a8351352170eb547cccd65011
/*
    viene mostrato come configurare il file .env sulla root
    e filesystem.php sotto /config
    praticamente si otterranno:
        'clientId'
        'clientSecret'
    tramite creazione di un app che accede alle api (io l'ho chiamata certOAuth)
    e poi viene anche spiegato come ottenere il 'refreshToken' utilizzato da laravel per le chiamate

	successivamente e praticamente semplice ed intuitivo utilizzare Storage di Laravel
	permette di estendere le funzionalità del FileSystem come se fosse in locale
	es:
	Storage::disk('google')->copy($file, "provvisori/001_p.doc");
	solo che è molto lento e per le operzione di copy, delete etc, mi sono affidato alle librerie
	di google client vedi ControllerProvvisori.php
*/


//ref per creazione progetto laravel con riferimento Google Drive
//https://www.luckymedia.dev/blog/laravel-project-setup-for-google-drive-api-integration-part-2




class mainController extends Controller
{
    public function __construct(){
    }

	function getClient($scope="docs")
	{
		/*	N.B. ho utilizzato DB su porta 8012
			da Console API Google (con una utenza loggata ovviamente)
			(N.B.:la console API deve essere quella dove dovranno essere presenti i documenti)
			https://console.cloud.google.com/apis/dashboard
			
			- abilitare una api (in questo caso google docs api e drive)
				--al servizio bisogna associare una credenziale
			- quindi su credenziali	creare un account di servizio
				--creare una chiave: sarà creato un json da scaricare e
				da deplyoare nel progetto e da settare durante il codice in setAuthConfig('credentials.json'):
				nel file c'è indicato l'utenza creata (a).
				In Google Docs, condividere il documento ed assegnare l'account di servizio creato (a)

			//per i doc da convertire in pdf:
			"require": {
				...,
				"barryvdh/laravel-dompdf": "^2.0",
				...
			}				

			
		*/
		$client = new Client();
		$client->setApplicationName('DOCS API PHP');
		if ($scope=="docs")
			$client->setScopes([\Google_Service_Docs::DOCUMENTS]);
		if ($scope=="list_drive")
			$client->setScopes([\Google_Service_Drive::DRIVE]);
		
		//$client->setScopes([\Google_Service_Drive::DRIVE_FILE]);
		
		$client->setAuthConfig('credentials.json');
		$client->setAccessType('offline');
		return $client;
	}

	public function edit_doc() {
		$client = $this->getClient("docs");
		
		$service = new \Google_Service_Docs($client);
		//open doc and edit
		$documentId = '12SFnAyha0znWrkajojOPek5CyFGj_1kq_e4ba1FgM9U';
		$doc = $service->documents->get($documentId);

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
		$allText[]="PRODOTTO";
		// Go through and create search/replace requests
		$requests = $textsAlreadyDone = $forEasyCompare = [];
		foreach ($allText as $currText) {
			if (in_array($currText, $textsAlreadyDone, true)) {
				// If two identical pieces of text are found only search-and-replace it once - no reason to do it multiple times
				continue;
			}

			if (preg_match_all("/(.*?)(PRODOTTO)(.*?)/", $currText, $matches, PREG_SET_ORDER)) {
				//NOTE: for simple static text searching you could of course just use strpos()
				// - and then loop on $matches wouldn't be necessary, and str_replace() would be simplified
				$modifiedText = $currText;
				foreach ($matches as $match) {
					$modifiedText = str_replace($match[0], $match[1] .'prodx'. $match[3], $modifiedText);
				}

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
		print_r($allText);
		echo "<hr>";
		print_r($forEasyCompare);
		if (count($forEasyCompare)>0) {
				$batchUpdateRequest = new \Google_Service_Docs_BatchUpdateDocumentRequest(['requests' => $requests]);
				$response = $service->documents->batchUpdate($documentId, $batchUpdateRequest);
				echo "OK";
		}

	
	}


	public function download_docs() {
		$client = $this->getClient("list_drive");
		
		$service = new \Google_Service_Drive($client);
		$folder="1lpPaUBt5DnmfDkJGeO1E5rGQyzy3XJuW";
		//per ricavare il folder basta vedere la url
		//attualmente punta sulla root del mio drive (cartella /docs)
		
		// Print the names and IDs for up to 10 files.
		$optParams = array(
          'q' => "'$folder' in parents and trashed=false",
          'spaces' => 'drive',
         
          'fields' => 'nextPageToken, files(id, name, mimeType, modifiedTime, size, parents)',
          'pageSize' => 1000,
          'includeItemsFromAllDrives' => true,
          'supportsAllDrives' => true,
          'corpora' => "allDrives",
		);
		$results = $service->files->listFiles($optParams);

		if (count($results->getFiles()) == 0) {
			print "No files found.\n";
		} else {
			print "Files:\n";
			foreach ($results->getFiles() as $file) {
				print_r($file);
				echo "<hr>";
				//printf("%s (%s)\n</br>", $file->getName(), $file->getId());
			}
		}		
	}
	
	public function test_load_pdf(Request $request) {
		$users=user::select('id','name')->orderBy('name')->get();
		
		$source = "master/008";
		return Pdf::loadView($source)->save('master/test.pdf')->stream('download.pdf');
		//return view('all_views/gestione/documenti_utili',compact('documenti_utili', $documenti_utili));
	}

    
    public function make_provv(Request $request)
    {
        $file="master/_001.doc";

        Storage::disk('google')->copy($file, "provvisori/001_p.doc");
        //$files =  Storage::disk('google')->files();
        //print_r($files);

        //$metadata = Storage::disk('google')->getAdapter()->getMetadata("1EHd7xUTM2E-2H4PnHGMIkgcQBzzoENlu");
        //echo "metadata $metadata";
        /*
        $file="001.doc";

            $id = Storage::disk("google")->getAdapter()->getMetadata($file)->extraMetadata()['id'];
            $filename = Storage::disk("google")->getAdapter()->getMetadata($file)->extraMetadata()['filename'];

           echo "id: $id filename $filename"; 
          */ 


        //return Storage::disk('google')->download($file);


        /*
         if (Storage::disk('google')->missing($file)) 
           echo "File mancante"; 
        else
            Storage::disk('google')->copy($file, "provvisori/001_p.doc");
        */
    }

    public function list_update(Request $request)
    {
        //Gdrive è solo un helper...posso anche usare nativamente Storage di Laravel
		//$items = Gdrive::all('/');
		// or
        $items = Gdrive::all('master');

        $v=response($items);
        $decodedData = json_decode($items, true);
        
        $dati=array();$indice=0;
        $data_update=date("Y-d-m H:i:s");
        
        foreach($decodedData as $task) {
            $id_doc=$task['extra_metadata']['id'];
            $real_name=$task['extra_metadata']['filename'];
            $resp=tbl_master::select('id')->where('id_doc','=',$id_doc)->get();

            if (!isset($resp[0]->id))
                $tbl_master= new tbl_master;
            else
                $tbl_master = tbl_master::find($resp[0]->id);

            $tbl_master->id_doc=$id_doc;
            $tbl_master->real_name=$real_name;
            $tbl_master->save();
        }


        //return response($items, 200);


    }   	
}	