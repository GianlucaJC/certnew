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


class ControllerMaster extends Controller
{
    public function __construct(){
    }

    public function revisioni($service,$fileId) {
        $revisions = $service->revisions->listRevisions($fileId);
        
		$revisioni=array();
		foreach ($revisions->getRevisions() as $revision) {
			$modTime = $revision->getModifiedTime();
			//echo "Revision ID: " . $revision->getId() . ", Modified Time: " . $modTime . "<br>";
			$revisioni[]=$revision;
		}
        
        return $revisioni;
    }

    public function load_rev(Request $request) {
        /////
        $id_ref=$request->input('id_ref');
            // create the Google client
            //controllare per invocare le richieste direttamente
            /*
            $client = new \Google_Client();
            $client->setClientId(env('GOOGLE_DRIVE_CLIENT_ID'));
            $client->setClientSecret(env('GOOGLE_DRIVE_CLIENT_SECRET'));
            $client->refreshToken(env('GOOGLE_DRIVE_REFRESH_TOKEN'));
            $service = new \Google_Service_Drive($client);

            $httpClient = $client->authorize();
            $response = $httpClient->get("https://www.googleapis.com/drive/v3/files/$id_ref");
            print_r($response);
            return;
            */
        $service=$this->open_doc($id_ref);
        $revisioni=$this->revisioni($service,$id_ref);
        return $revisioni;
    }

    public function change_master(Request $request) {
        $id_doc=$request->input('id_doc');
        $id_clone_from=$request->input('id_clone_from');
        $info=tbl_master::select('real_name')->where('id_doc','=',$id_clone_from)->first();
        $master_name="temp";
        if (isset($info->real_name)) $master_name=$info->real_name;
        
        $client = new \Google_Client();
        $client->setClientId(env('GOOGLE_DRIVE_CLIENT_ID'));
        $client->setClientSecret(env('GOOGLE_DRIVE_CLIENT_SECRET'));
        $client->refreshToken(env('GOOGLE_DRIVE_REFRESH_TOKEN'));
        $service = new \Google_Service_Drive($client);
        
        
        //crea nuovo master da clone
        $id_folder="1OWWv1lv28wsv3wJsIzqAeg4VQ4r1e8Fe"; //cartella master statica
        $googleServiceDriveFile = new \Google_Service_Drive_DriveFile([
            'name' => $master_name,
            'parents' => [$id_folder]
        ]);

        $fileId = $service->files->copy($id_doc, $googleServiceDriveFile, ['fields' => 'id']);
        $esito['header']="OK";
        $esito['fileId']=$fileId->id;

        //cancella il clone dal cloud
        $service->files->delete($id_doc);
        
        //cancella il clone dal db
        $dele=tbl_master::from('tbl_master')
        ->where('id_doc','=',$id_doc)   
        ->delete(); 

        //rende osboleto il vecchio master aggiornando il DB
        $name_obs=$master_name."_obs";
        $obsoleto=tbl_master::from('tbl_master')->where('id_doc','=',$id_clone_from)->update(['real_name'=>$name_obs,'obsoleti'=>1]);
        //rinomina sul cloud il vecchio master in _obs
        $file = new \Google_Service_Drive_DriveFile();
        $file->setName($name_obs);
        $updatedFile = $service->files->update($id_clone_from, $file);


        //inserisce il nuovo master nel DB e dichiara che ha degli obsoleti
        $tbl_master= new tbl_master;
        $tbl_master->id_doc=$fileId->id;
        $tbl_master->real_name=$master_name;
        $tbl_master->obsoleti=2;
        $tbl_master->save();        
        echo json_encode($esito);

    }

    public function duplica_master(Request $request) {
        $id_doc=$request->input('id_doc');
        $name_clone=$request->input('name_clone');

        $client = new \Google_Client();
        $client->setClientId(env('GOOGLE_DRIVE_CLIENT_ID'));
        $client->setClientSecret(env('GOOGLE_DRIVE_CLIENT_SECRET'));
        $client->refreshToken(env('GOOGLE_DRIVE_REFRESH_TOKEN'));
        $service = new \Google_Service_Drive($client);
        
        
        $id_folder="1OWWv1lv28wsv3wJsIzqAeg4VQ4r1e8Fe"; //cartella master statica
        $googleServiceDriveFile = new \Google_Service_Drive_DriveFile([
            'name' => $name_clone,
            'parents' => [$id_folder]
        ]);

        $fileId = $service->files->copy($id_doc, $googleServiceDriveFile, ['fields' => 'id']);
        $esito['header']="OK";
        $esito['fileId']=$fileId;

        $check_dele=tbl_master::select('id_doc')->where('id_clone_from','=',$id_doc)->first();
        if (isset($check_dele->id_doc)) {
            $id_doc_dele=$check_dele->id_doc;
            $delete = Storage::disk('google')->delete($id_doc_dele);
        }     

        $dele=tbl_master::from('tbl_master')
        ->where('id_clone_from','=',$id_doc)   
        ->delete(); 


        
        
        
        $tbl_master= new tbl_master;
        $tbl_master->id_doc=$fileId->id;
        $tbl_master->id_clone_from=$id_doc;
        $tbl_master->real_name=$name_clone;
        $tbl_master->save();
      
        
        echo json_encode($esito);

    }



	public function open_doc($fileId) {
        $client = new \Google_Client();
        $client->setClientId(env('GOOGLE_DRIVE_CLIENT_ID'));
        $client->setClientSecret(env('GOOGLE_DRIVE_CLIENT_SECRET'));
        $client->refreshToken(env('GOOGLE_DRIVE_REFRESH_TOKEN'));
        $service = new \Google_Service_Drive($client);
		return $service;
	}    

	public function elenco_master(Request $request) {
        $cerca_coa=$request->input('cerca_coa');
        
        // Se la richiesta è per la verifica dei tag, la gestiamo qui
        if ($request->has('action') && $request->input('action') == 'verifica_tag') {
            $ids_json = $request->input('ids', '[]');
            // L'input 'ids' arriva come una stringa JSON, quindi la decodifichiamo in un array PHP.
            $ids = json_decode($ids_json, true);
            // Se la decodifica fallisce o non produce un array, usiamo un array vuoto per sicurezza.
            if (!is_array($ids)) $ids = [];
            $result = $this->verifica_tag_master($ids);
            return response()->json($result);
        }
        
        // NUOVA LOGICA: Gestisce la richiesta per ottenere tutti gli ID filtrati
        if ($request->has('action') && $request->input('action') == 'get_all_filtered_ids') {
            try {
                $query = tbl_master::from('tbl_master as m')
                    ->select('m.id_doc') // Selezioniamo solo l'id_doc
                    ->where('m.dele', '=', 0)
                    ->where('m.obsoleti', '<>', 1);

                // Applichiamo gli stessi filtri della tabella

                // Gestione del filtro custom per i tag essenziali mancanti
                $customFilter = $request->input('custom_filter');
                if ($customFilter === 'tag_essenziali_mancanti') {
                    $query->where(function($q) {
                        $q->where(DB::raw("!LOCATE('lt', m.tags_found) OR !LOCATE('exp', m.tags_found) OR !LOCATE('pdate', m.tags_found)"));
                    })
                    ->whereNotNull('m.last_scan');
                }

                // Applica ricerca per colonna (se presente nella richiesta)
                $columns = $request->input('columns');
                $hasColumnSearch = false;
                if ($columns) {
                    foreach ($columns as $column) {
                        if (!empty($column['search']['value'])) {
                            $hasColumnSearch = true;
                            break;
                        }
                    }
                }

                if ($hasColumnSearch) {
                    if ($columns) {
                        $searchValue = $columns[1]['search']['value'] ?? null;
                        if ($searchValue) { $query->where('m.real_name', 'like', '%'.$searchValue.'%'); }

                        $searchValue = $columns[2]['search']['value'] ?? null;
                        if ($searchValue) {
                            $query->where(function($q) use ($searchValue) {
                                $q->where('m.rev', 'like', '%'.$searchValue.'%')
                                  ->orWhere('m.data_rev', 'like', '%'.$searchValue.'%')
                                  ->orWhere(DB::raw("DATE_FORMAT(m.data_rev, '%d-%m-%Y')"), 'like', '%'.$searchValue.'%');
                            });
                        }

                        $searchValue = $columns[4]['search']['value'] ?? null;
                        if ($searchValue) {
                            if (strpos(strtolower($searchValue), 'mai') !== false) { $query->whereNull('m.last_scan'); } 
                            else { $query->where('m.tags_found', 'like', '%'.$searchValue.'%'); }
                        }
                    }
                } elseif (!empty($request->input('search.value'))) {
                    // Ricerca globale
                    $searchValue = $request->input('search.value');
                    $query->where('m.real_name', 'like', "%{$searchValue}%");
                }

                $all_ids = $query->pluck('m.id_doc')->all();
                return response()->json(['success' => true, 'ids' => $all_ids]);
            } catch (\Exception $e) {
                \Log::error("Errore get_all_filtered_ids: " . $e->getMessage());
                return response()->json(['success' => false, 'message' => 'Errore del server nel recuperare gli ID.'], 500);
            }
        }

        // Controllo robusto per DataTables: verifica se è una richiesta AJAX o se contiene il parametro 'draw'.
        if ($request->ajax() || $request->has('draw')) {
            try {
                $query = tbl_master::from('tbl_master as m')
                    ->select('m.id', 'm.id_doc', 'm.id_clone_from', 'm.real_name', 'm.obsoleti', 'm.rev', 'm.data_rev', 'm.created_at', 'm.updated_at', 'm.last_scan', 'm.tags_found', 'm.sistemato')
                    ->where('m.dele', '=', 0)
                    ->where('m.obsoleti', '<>', 1);

                // Filtro per 'sistemato'
                $customFilterSistemato = $request->input('custom_filter_sistemato');
                if ($customFilterSistemato === 'sistemati') {
                    $query->where('m.sistemato', '=', 1);
                } elseif ($customFilterSistemato === 'non_sistemati') {
                    $query->where('m.sistemato', '=', 0);
                }

                // Conteggio totale record senza filtri (ma con le clausole where iniziali e tutte le select)
                $totalData = $query->clone()->count();

                $filteredQuery = $query->clone(); // Utilizza un oggetto query separato per il filtraggio

                // Gestione del filtro custom per i tag essenziali mancanti
                $customFilter = $request->input('custom_filter');
                if ($customFilter === 'tag_essenziali_mancanti') {
                    $filteredQuery->where(function($q) {
                        $q->where(DB::raw("!LOCATE('lt', m.tags_found) OR !LOCATE('exp', m.tags_found) OR !LOCATE('pdate', m.tags_found)"));
                    })
                    // Aggiungiamo questa condizione per escludere quelli mai scansionati, che non hanno tag rossi ma una dicitura a parte
                    ->whereNotNull('m.last_scan');
                }

                // Gestione del filtro custom per i sistemati
                $customFilterSistemato = $request->input('custom_filter_sistemato');
                if ($customFilterSistemato === 'sistemati') {
                    $filteredQuery->where('m.sistemato', '=', 1);
                } elseif ($customFilterSistemato === 'non_sistemati') {
                    $filteredQuery->where('m.sistemato', '=', 0);
                }

                // Applica ricerca per colonna
                $columns = $request->input('columns');
                $hasColumnSearch = false;
                if ($columns) {
                    foreach ($columns as $column) {
                        if (!empty($column['search']['value'])) {
                            $hasColumnSearch = true;
                            break;
                        }
                    }
                }

                if ($hasColumnSearch) {
                    // Se c'è almeno una ricerca per colonna, la applichiamo
                    if ($columns) {
                        $searchValue = $columns[1]['search']['value'] ?? null;
                        if ($searchValue) { // real_name
                            $filteredQuery->where('m.real_name', 'like', '%'.$searchValue.'%');
                        }

                        $searchValue = $columns[2]['search']['value'] ?? null;
                        if ($searchValue) { // Colonna Rev (combinata)
                            $filteredQuery->where(function($q) use ($searchValue) {
                                $q->where('m.rev', 'like', '%'.$searchValue.'%')
                                  ->orWhere('m.data_rev', 'like', '%'.$searchValue.'%')
                                  ->orWhere(DB::raw("DATE_FORMAT(m.data_rev, '%d-%m-%Y')"), 'like', '%'.$searchValue.'%');
                            });
                        }

                        $searchValue = $columns[4]['search']['value'] ?? null;
                        if ($searchValue) { // Tag Rilevati
                            $searchTag = strtolower(trim($searchValue));
                            if (!empty($searchTag) && $searchTag !== 'mai') {
                                $filteredQuery->where('m.tags_found', 'like', '%'.$searchTag.'%');
                            } elseif (strpos($searchTag, 'mai') !== false || strpos($searchTag, 'rosso') !== false) {
                                $filteredQuery->whereNull('m.last_scan');
                            } else {
                                $filteredQuery->whereRaw('1 = 0'); // Nessun risultato se il tag non è valido
                            }
                        }
                    }
                } elseif (!empty($request->input('search.value'))) {
                    // Altrimenti, se non ci sono ricerche per colonna, applichiamo la ricerca globale
                    $searchValue = $request->input('search.value');
                    $filteredQuery->where(function($q) use ($searchValue) {
                        $q->where('m.real_name', 'like', "%{$searchValue}%")
                          ->orWhere('m.rev', 'like', "%{$searchValue}%");
                    });
                }
                // Conteggio record dopo filtri
                $totalFiltered = $filteredQuery->clone()->count(); // Clona prima di ordinare/limitare

                // Ordinamento
                $order = $request->input('order.0');
                if (!empty($order)) {
                    $orderColumnIndex = $order['column'];
                    $orderDirection = $order['dir'];
                    $columnName = $request->input("columns.{$orderColumnIndex}.name"); // Questo dovrebbe essere il 'name' dalla configurazione delle colonne JS
                    if ($columnName) {
                        $filteredQuery->orderBy($columnName, $orderDirection);
                    }
                }

                // Paginazione
                $start = $request->input('start', 0);
                $length = $request->input('length', 10);
                $data = $filteredQuery->offset($start)->limit($length)->get();

                return response()->json([
                    "draw"            => intval($request->input('draw')),
                    "recordsTotal"    => intval($totalData),
                    "recordsFiltered" => intval($totalFiltered),
                    "data"            => $data
                ]);
            } catch (\Exception $e) {
                // Logga l'errore per il debug
                \Log::error("Errore DataTables in ControllerMaster@elenco_master: " . $e->getMessage());
                // Restituisci una risposta JSON vuota ma valida per evitare l'alert di DataTables
                return response()->json([
                    "draw"            => intval($request->input('draw')),
                    "recordsTotal"    => 0,
                    "recordsFiltered" => 0,
                    "data"            => [],
                    "error"           => "Si è verificato un errore sul server." // Opzionale: per debug
                ]);
            }
        }

		return view('all_views/master/elenco_master');
    }

    public function dele_master(Request $request) {




        $id_ref=$request->input('id_ref');
        $dele=tbl_master::where('id','=',$id_ref)->update(['dele' =>1]);
        $esito['header']="OK";
        $esito['dele']=$dele;
        echo json_encode($esito);
    }

    function to_def(Request $request) {
        $id_provv=$request->input('id_provv');
        $stato=$request->input('from');
        $id_doc=$request->input('id_doc');
        $codice_master=$request->input('codice_master');
        $up=cert_provvisori::where('id','=',$id_provv)->update(['stato' =>$stato]);
       

        $client = new \Google_Client();
        $client->setClientId(env('GOOGLE_DRIVE_CLIENT_ID'));
        $client->setClientSecret(env('GOOGLE_DRIVE_CLIENT_SECRET'));
        $client->refreshToken(env('GOOGLE_DRIVE_REFRESH_TOKEN'));
        $drive = new Drive($client);
        $content=$drive->files->export($id_doc, 'application/pdf');
        $txt=$content->getBody()->getContents();
      
        $fold="";
        if ($stato=="2") $fold="definitivi_idonei";
        if ($stato=="3") $fold="definitivi_non_idonei";
        $filename="doc/$fold/$codice_master.pdf";
        $attempt = 1;$header="OK";
        do{
            //Wait 5000ms
            usleep(500000*$attempt);
            //Try to get pdf file.
            $content=$drive->files->export($id_doc, 'application/pdf', array( 'alt' => 'media' ));
            //Save just fetched data.
            file_put_contents($filename, $content->getBody()->getContents());
            if(filesize($filename)) break;
            else $attempt++;
            if ($attempt>10) $header="KO";
          }while(true);
          
          $esito['up']=$up;
          $esito['header']=$header;
          $esito['attesa']=$attempt;

        echo json_encode($esito);
    }

    public function save_master(Request $request) {
        $id_ref=$request->input('id_ref');
        $name_master_edit=$request->input('name_master_edit');
        $rev_edit=$request->input('rev_edit');
        $data_rev_edit=$request->input('data_rev_edit');
        if ($id_ref==0) {
            $id_doc=$this->new_master($name_master_edit);
            $tbl_master= new tbl_master;
            $tbl_master->id_doc=$id_doc;
            $tbl_master->save();
            $id_ref=$tbl_master->id;
        }
        
        $save=tbl_master::where('id','=',$id_ref)->update(['real_name' =>$name_master_edit, 'rev'=>$rev_edit,'data_rev'=>$data_rev_edit]);
        $esito['header']="OK";
        $esito['dele']=$save;
        echo json_encode($esito);
    }   

    function new_master($filename_doc="Nuovo master")
    {
        /*
            se non vengono letti i valori in .env
                php artisan config:cache
                php artisan config:clear
                php artisan cache:clear
        */

        $client = new \Google_Client();
        $client->setClientId(env('GOOGLE_DRIVE_CLIENT_ID'));
        $client->setClientSecret(env('GOOGLE_DRIVE_CLIENT_SECRET'));
        $client->refreshToken(env('GOOGLE_DRIVE_REFRESH_TOKEN'));
        $service = new \Google_Service_Drive($client);
        

        $id_folder_master="1OWWv1lv28wsv3wJsIzqAeg4VQ4r1e8Fe"; //cartella master statica

        $googleServiceDriveFile = new \Google_Service_Drive_DriveFile([
            'name' => $filename_doc,
            'parents' => [$id_folder_master]
        ]);

        //id_master: id riferito ad un master vuoto posto sulla root
        $id_master="1BEgkvJ3rrfzXqc7PDTWkqP8e37F9kwoEFAYeypEAXog";

        $fileId = $service->files->copy($id_master, $googleServiceDriveFile, ['fields' => 'id']);
        return $fileId->id;
        
    } 

    public function verifica_tag_master($ids) {
        // I tag da verificare ora arrivano dalla richiesta, ma per ora li ignoriamo e cerchiamo TUTTI i tag.
        // La logica per usare i tag specifici può essere aggiunta se necessario.
        // $tags_to_check = json_decode($request->input('tags', '[]'), true);

        $client = new \Google_Client();
        $client->setClientId(env('GOOGLE_DRIVE_CLIENT_ID'));
        $client->setClientSecret(env('GOOGLE_DRIVE_CLIENT_SECRET'));
        $client->refreshToken(env('GOOGLE_DRIVE_REFRESH_TOKEN'));
        $service = new \Google_Service_Drive($client);
    
        $processed_count = 0;
    
        foreach ($ids as $id_doc) {
            try {
                $response = $service->files->export($id_doc, 'text/html', ['alt' => 'media']);
                $content = $response->getBody()->getContents();
    
                // Regex unificata per trovare tutti i tipi di tag: [[tag]], [tag], &lt;tag&gt;, $tag$
                // Il gruppo di cattura interno ([a-zA-Z0-9_]+) estrae solo il nome del tag.
                $pattern = '/(?:\[\[([a-zA-Z0-9_]+)\]\]|\[([a-zA-Z0-9_]+)\]|&lt;([a-zA-Z0-9_]+)&gt;|\$([a-zA-Z0-9_]+)\$)/';
                preg_match_all($pattern, $content, $matches);
    
                // Uniamo tutti i nomi dei tag catturati dai diversi gruppi della regex
                $found_tags = [];
                for ($i = 1; $i < count($matches); $i++) {
                    $found_tags = array_merge($found_tags, array_filter($matches[$i]));
                }
    
                // Rimuoviamo i tag 'firma' e 'firma_d' dall'array dei tag trovati.
                $filtered_tags = array_filter($found_tags, function($tag) {
                    return !in_array($tag, ['firma', 'firma_d']);
                });

                // Rimuoviamo duplicati e ordiniamo i tag filtrati.
                $unique_tags = array_unique($filtered_tags);
                sort($unique_tags);

                // Convertiamo l'array di tag in una stringa separata da virgole
                $tags_string = implode(',', $unique_tags);
    
                // Aggiorniamo il DB con la stringa dei tag e la data di scansione
                tbl_master::where('id_doc', $id_doc)->update([
                    'tags_found' => $tags_string,
                    'last_scan' => now()
                ]);
    
                $processed_count++;
                
            } catch (\Exception $e) {
                // Logga l'errore o gestiscilo come preferisci
                // Per ora, continuiamo con il prossimo documento
                \Log::error("Errore durante la verifica dei tag per il doc ID: $id_doc - " . $e->getMessage());
                continue;
            }
        }
    
        return [
            'success' => true, 
            'message' => "Verifica completata. Documenti processati: " . $processed_count . "/" . count($ids),
            'processed' => $processed_count,
            'total' => count($ids)
        ];
    }

    public function toggle_sistemato(Request $request)
    {
        try {
            $id_doc = $request->input('id_doc');
            if (!$id_doc) {
                return response()->json(['success' => false, 'message' => 'ID documento non fornito.'], 400);
            }
            $master = tbl_master::where('id_doc', $id_doc)->first();

            if ($master) {
                $master->sistemato = !$master->sistemato;
                $master->save();
                return response()->json(['success' => true, 'message' => 'Stato aggiornato con successo.']);
            }

            return response()->json(['success' => false, 'message' => 'Master non trovato.'], 404);
        } catch (\Exception $e) {
            \Log::error("Errore in toggle_sistemato: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Errore del server.'], 500);
        }
    }

    public function archive_master(Request $request)
    {
        try {
            $id_doc = $request->input('id_doc');
            if (!$id_doc) {
                return response()->json(['success' => false, 'message' => 'ID documento non fornito.'], 400);
            }

            $master = tbl_master::where('id_doc', $id_doc)->first();
            if (!$master) {
                return response()->json(['success' => false, 'message' => 'Master non trovato.'], 404);
            }

            /*
            $client = new \Google_Client();
            $client->setClientId(env('GOOGLE_DRIVE_CLIENT_ID'));
            $client->setClientSecret(env('GOOGLE_DRIVE_CLIENT_SECRET'));
            $client->refreshToken(env('GOOGLE_DRIVE_REFRESH_TOKEN'));
            $service = new \Google_Service_Drive($client);

            $newName = $master->real_name . "_obs";
            $file = new \Google_Service_Drive_DriveFile(['name' => $newName]);
            $service->files->update($id_doc, $file, ['fields' => 'id, name']);
            */

            $master->obsoleti = 1;
            $master->save();

            return response()->json(['success' => true, 'message' => 'Master archiviato con successo.']);
        } catch (\Exception $e) {
            \Log::error("Errore in archive_master: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Errore del server durante l\'archiviazione.'], 500);
        }
    }

}	