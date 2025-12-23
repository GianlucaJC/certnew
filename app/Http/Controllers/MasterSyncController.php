<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use App\Models\tbl_master;
use Illuminate\Support\Facades\Log;

class MasterSyncController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view('all_views.master.sincro_master');
    }

    /**
     * Sincronizza i file master dalla cartella locale 'public/doc/master' al database.
     * 
     * Questa funzione scansiona la cartella locale e rileva sia i file **nuovi** che quelli **aggiornati**.
     * I file rilevati vengono aggiunti o aggiornati nella tabella `tbl_master` come "pendenti",
     * pronti per essere caricati su Google Drive.
     * 
     * La logica di rilevamento è la seguente:
     * 1.  **File Nuovo**: Se un file non è presente nel database, viene aggiunto come "pendente".
     * 2.  **File Aggiornato**: Se un file è già presente nel database, la funzione confronta la data di ultima
     *     modifica del file locale con quella registrata (`local_last_modified`). Se il file locale è più
     *     recente, viene nuovamente marcato come "pendente" per consentire la sovrascrittura su Drive.
     *
     * Durante la scansione, vengono saltati i seguenti file:
     * - File temporanei o di sistema (es. che iniziano con '~' o 'thumbs.db').
     * - File già presenti e non modificati rispetto all'ultima sincronizzazione.
     *
     * I file pendenti vengono identificati da un `id_doc` nel formato 'local_file_[nomefile]'.
     * 
     * **Importante:** Questa logica richiede una colonna `local_last_modified` (DATETIME o TIMESTAMP)
     * nella tabella `tbl_master` per funzionare correttamente.
     *
     * @param Request $request La richiesta HTTP (attualmente non utilizzata per parametri specifici).
     * @return \Illuminate\Http\JsonResponse
     * Restituisce un oggetto JSON con l'esito, il messaggio, l'elenco dei file aggiunti,
     * il totale dei file scansionati e il numero di file processati.
     */
    public function sync(Request $request)
    {
        try {
            $localMasterPath = public_path('doc/master');
            if (!File::exists($localMasterPath)) {
                return response()->json(['success' => false, 'message' => 'La cartella public/doc/master non esiste.'], 404);
            }

            // Scansiona sempre l'intera cartella. La logica rileva automaticamente file nuovi e aggiornati.
            $filesToScan = File::files($localMasterPath);
            $addedFiles = [];
            $totalFiles = count($filesToScan);
            
            foreach ($filesToScan as $file) {
                $filenameWithExtension = $file->getFilename();
                $filenameWithoutExtension = pathinfo($filenameWithExtension, PATHINFO_FILENAME);
                $localFileTimestamp = $file->getMTime();
                
                // Salta file temporanei o di sistema
                if (str_starts_with($filenameWithExtension, '~') || strtolower($filenameWithExtension) === 'thumbs.db') {
                    continue;
                }

                // Query più robusta: cerca il record "attivo" (non pendente) per evitare ambiguità
                // se esistono duplicati o record pendenti.
                $master = tbl_master::where('real_name', $filenameWithoutExtension)
                                    ->where('id_doc', 'NOT LIKE', 'local_file_%')
                                    ->first();

                if (!$master) {
                    // --- CASO 1: FILE NUOVO ---
                    // Non è stato trovato un master "ufficiale" (su Drive).
                    // Verifichiamo se esiste già un record "pendente" per questo file.
                    $pendingMasterExists = tbl_master::where('id_doc', 'local_file_' . $filenameWithoutExtension)->exists();

                    if (!$pendingMasterExists) {
                        // Solo se non esiste NESSUN record per questo file, lo creiamo come pendente.
                        $newMaster = new tbl_master();
                        $newMaster->real_name = $filenameWithoutExtension;
                        $newMaster->id_doc = 'local_file_' . $filenameWithoutExtension;
                        $newMaster->local_last_modified = date('Y-m-d H:i:s', $localFileTimestamp);
                        $newMaster->save();
                        $addedFiles[] = $filenameWithExtension;
                    }
                } else {
                    // --- CASO 2: FILE ESISTENTE ---
                    // Se il file è già marcato come pendente, lo saltiamo per evitare di riproporlo.
                    if (str_starts_with($master->id_doc, 'local_file_')) {
                        continue;
                    }

                    // Confronta la data di modifica del file con quella nel DB.
                    $dbTimestamp = strtotime($master->local_last_modified);

                    if ($localFileTimestamp > $dbTimestamp) {
                        // Il file locale è stato aggiornato, lo marchiamo di nuovo come pendente.
                        $master->id_doc = 'local_file_' . $filenameWithoutExtension;
                        $master->local_last_modified = date('Y-m-d H:i:s', $localFileTimestamp);
                        $master->obsoleti = 0; // Assicurati che non sia escluso
                        $master->save();
                        $addedFiles[] = $filenameWithExtension;
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Sincronizzazione completata.',
                'added_files' => $addedFiles,
                'total_files_scanned' => $totalFiles,
                'processed_count' => count($addedFiles)
            ]);

        } catch (\Exception $e) {
            Log::error("Errore durante la sincronizzazione dei master locali: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Errore del server durante la sincronizzazione.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function checkPending(Request $request)
    {
        try {
            $pendingFiles = tbl_master::where('id_doc', 'LIKE', 'local_file_%')
                                      ->where('obsoleti', '<>', 3) // Escludi i file marcati come esclusi
                                      ->pluck('real_name')
                                      ->map(function ($name) {
                                          // Aggiunge l'estensione .doc per coerenza con l'output della sincronizzazione
                                          return $name . '.doc';
                                      })
                                      ->toArray();

            return response()->json([
                'success' => true,
                'pending_files' => $pendingFiles
            ]);
        } catch (\Exception $e) {
            Log::error("Errore durante il controllo dei file pendenti: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Errore del server durante il controllo dei file pendenti.'
            ], 500);
        }
    }
    public function uploadToDrive(Request $request)
    {
        $filename = $request->input('filename');
        $overwrite = $request->input('overwrite', false);
        if (!$filename) {
            return response()->json(['success' => false, 'message' => 'Nome file non fornito.'], 400);
        }

        try {
            $localFilePath = public_path('doc/master/' . $filename);
            if (!File::exists($localFilePath)) {
                return response()->json(['success' => false, 'message' => "File locale non trovato: $filename"], 404);
            }

            // Rimuove l'estensione, che potrebbe essere .doc o .docx
            $filenameWithoutExtension = preg_replace('/\\.[^.\\s]{3,4}$/', '', $filename);

            // Controlla se un master con questo nome esiste già su Google Drive (non un file locale)
            $master = tbl_master::where('real_name', $filenameWithoutExtension)
                                ->where('id_doc', 'NOT LIKE', 'local_file_%')
                                ->first();

            // Se il master esiste e NON si vuole sovrascrivere, salta il file.
            if ($master && !$overwrite) {
                return response()->json(['success' => true, 'skipped' => true, 'message' => "File $filename saltato perché esiste già e la sovrascrittura non è abilitata."]);
            }

            // Inizializza il client di Google
            $client = new \Google_Client();
            $client->setClientId(env('GOOGLE_DRIVE_CLIENT_ID'));
            $client->setClientSecret(env('GOOGLE_DRIVE_CLIENT_SECRET'));
            $client->refreshToken(env('GOOGLE_DRIVE_REFRESH_TOKEN'));
            $service = new \Google_Service_Drive($client);

            if ($master && $overwrite) {
                // --- LOGICA DI SOVRASCRITTURA ---
                if (!$master) {
                    return response()->json(['success' => false, 'message' => "Master '$filenameWithoutExtension' non trovato nel database per la sovrascrittura."], 404);
                }

                $fileId = $master->id_doc;
                $emptyFile = new \Google_Service_Drive_DriveFile(); // File vuoto per l'update

                $updatedFile = $service->files->update($fileId, $emptyFile, [ // Correzione qui
                    'data' => File::get($localFilePath),
                    'uploadType' => 'multipart',
                    'fields' => 'id',
                ]);

                // Aggiorna il record principale con la data di modifica attuale
                $master->local_last_modified = now();
                $master->save();

                // Rimuovi l'eventuale record 'local_file' duplicato che è stato creato dalla sincronizzazione
                tbl_master::where('real_name', $filenameWithoutExtension)
                          ->where('id_doc', 'like', 'local_file_%')
                          ->delete();
                
                return response()->json(['success' => true, 'message' => "File $filename sovrascritto con successo.", 'updated_id' => $updatedFile->id]);

            } else {
                // --- LOGICA DI CREAZIONE (comportamento originale) ---
                $id_folder_master = "1OWWv1lv28wsv3wJsIzqAeg4VQ4r1e8Fe"; // cartella master statica
                $googleFile = new \Google_Service_Drive_DriveFile([
                    'name' => $filenameWithoutExtension,
                    'parents' => [$id_folder_master],
                ]);
                $createdFile = $service->files->create($googleFile, [
                    'data' => File::get($localFilePath),
                    'mimeType' => 'application/msword',
                    'uploadType' => 'multipart',
                    'fields' => 'id'
                ]);

                // Aggiorna il record 'local_file' con il nuovo ID di Drive e la data di modifica
                tbl_master::where('real_name', $filenameWithoutExtension)
                          ->where('id_doc', 'like', 'local_file_%')
                          ->update(['id_doc' => $createdFile->id, 'local_last_modified' => now()]);

                return response()->json(['success' => true, 'message' => "File $filename caricato con successo.", 'new_id' => $createdFile->id]);
            }

        } catch (\Exception $e) {
            Log::error("Errore durante il caricamento su Drive del file $filename: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => "Errore server durante il caricamento di $filename.", 'error' => $e->getMessage()], 500);
        }
    }

    public function uploadLocal(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:doc,docx|max:5120', // max 5MB
        ]);

        try {
            $file = $request->file('file');
            $filename = $file->getClientOriginalName();
            $localMasterPath = public_path('doc/master');

            if (!File::exists($localMasterPath)) {
                File::makeDirectory($localMasterPath, 0755, true);
            }

            // Sposta il file nella cartella di destinazione
            $file->move($localMasterPath, $filename);

            // --- NUOVA LOGICA: REGISTRA IL FILE NEL DATABASE ---
            $filenameWithoutExtension = pathinfo($filename, PATHINFO_FILENAME);

            // Cerca un master esistente con lo stesso nome, indipendentemente dal suo stato.
            // Diamo priorità al record che è già su Drive per l'aggiornamento.
            $master = tbl_master::where('real_name', $filenameWithoutExtension)
                                ->where('id_doc', 'NOT LIKE', 'local_file_%')
                                ->first();

            if ($master) {
                // Se il master esiste già, lo aggiorniamo per marcarlo come "pendente".
                $master->id_doc = 'local_file_' . $filenameWithoutExtension;
                $master->local_last_modified = now();
                $master->obsoleti = 0; // Assicurati che non sia escluso
                $master->save();
            } else {
                // Se non esiste un master "ufficiale", controlliamo se esiste già un record "pendente".
                $pendingMaster = tbl_master::where('id_doc', 'local_file_' . $filenameWithoutExtension)->first();
                if ($pendingMaster) {
                    // Se esiste già un pendente, aggiorniamo solo la sua data di modifica.
                    $pendingMaster->local_last_modified = now();
                    $pendingMaster->save();
                } else {
                    // Altrimenti, creiamo un nuovo record pendente.
                    $newMaster = new tbl_master();
                    $newMaster->real_name = $filenameWithoutExtension;
                    $newMaster->id_doc = 'local_file_' . $filenameWithoutExtension;
                    $newMaster->local_last_modified = now();
                    $newMaster->save();
                }
            }

            return response()->json(['success' => true, 'message' => "File $filename caricato con successo."]);

        } catch (\Exception $e) {
            Log::error("Errore durante l'upload del file locale: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Errore del server durante il caricamento del file.', 'error' => $e->getMessage()], 500);
        }
    }

    public function excludeFiles(Request $request)
    {
        try {
            $filenames = $request->input('filenames', []);
            if (empty($filenames)) {
                return response()->json(['success' => false, 'message' => 'Nessun file selezionato.'], 400);
            }

            // Usa il campo 'obsoleti' con valore 3 per marcare come escluso
            // Rimuovo l'estensione dai nomi dei file prima di cercare nel DB
            $realNames = array_map(function ($filename) {
                return pathinfo($filename, PATHINFO_FILENAME);
            }, $filenames);

            tbl_master::whereIn('real_name', $realNames)
                      ->where('id_doc', 'like', 'local_file_%')
                      ->update(['obsoleti' => 3]);

            return response()->json(['success' => true, 'message' => 'File esclusi con successo.']);
        } catch (\Exception $e) {
            Log::error("Errore in excludeFiles: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Errore del server: ' . $e->getMessage()], 500);
        }
    }

    public function getExcludedFiles()
    {
        try {
            // Recupera i file marcati come esclusi (obsoleti = 3)
            $excludedFiles = tbl_master::where('dele', 0)->where('obsoleti', 3)->pluck('real_name')->toArray();
            return response()->json(['success' => true, 'excluded_files' => $excludedFiles]);
        } catch (\Exception $e) {
            Log::error("Errore in getExcludedFiles: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Errore del server: ' . $e->getMessage()], 500);
        }
    }

    public function restoreFiles(Request $request)
    {
        try {
            $filenames = $request->input('filenames', []);
            if (empty($filenames)) {
                return response()->json(['success' => false, 'message' => 'Nessun file selezionato.'], 400);
            }

            // Ripristina i file riportando 'obsoleti' a 0
            tbl_master::whereIn('real_name', $filenames)->where('obsoleti', 3)->update(['obsoleti' => 0]);

            return response()->json(['success' => true, 'message' => 'File ripristinati con successo.']);
        } catch (\Exception $e) {
            Log::error("Errore in restoreFiles: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Errore del server: ' . $e->getMessage()], 500);
        }
    }
}