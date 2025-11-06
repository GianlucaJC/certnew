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

    public function sync(Request $request)
    {
        try {
            $localMasterPath = public_path('doc/master');
            if (!File::exists($localMasterPath)) {
                return response()->json(['success' => false, 'message' => 'La cartella public/doc/master non esiste.'], 404);
            }

            $localFiles = File::files($localMasterPath);
            $addedFiles = [];
            $totalFiles = count($localFiles);
            $processedCount = 0;

            // Get existing real_names from tbl_master, excluding those already marked as local
            $existingMasterNames = tbl_master::where('id_doc', 'NOT LIKE', 'local_file_%')
                                            ->pluck('real_name')
                                            ->toArray();
            // Also get existing local files from tbl_master to avoid re-adding them
            $existingLocalMasterNames = tbl_master::where('id_doc', 'LIKE', 'local_file_%')
                                                ->pluck('real_name')
                                                ->toArray();

            foreach ($localFiles as $file) {
                $filenameWithExtension = $file->getFilename();
                $filenameWithoutExtension = pathinfo($filenameWithExtension, PATHINFO_FILENAME);

                // Condizione per non inserire file temporanei o di sistema
                if (str_starts_with($filenameWithExtension, '~') || strtolower($filenameWithExtension) === 'thumbs.db') {
                    continue; // Salta i file
                }

                // Check if this file (by its real_name) already exists in tbl_master
                // either as a Google Drive master or as an already synced local file
                if (in_array($filenameWithoutExtension, $existingMasterNames) || in_array($filenameWithoutExtension, $existingLocalMasterNames)) {
                    $processedCount++;
                    continue; // Skip if already present
                }

                // Add to tbl_master
                $master = new tbl_master();
                $master->real_name = $filenameWithoutExtension;
                // Use a unique identifier for local files in id_doc
                $master->id_doc = 'local_file_' . $filenameWithoutExtension;
                $master->dele = 0;
                $master->obsoleti = 0;
                $master->sistemato = 0;
                // Other fields can be null or default
                $master->save();

                $addedFiles[] = $filenameWithExtension;
                $processedCount++;
            }

            return response()->json([
                'success' => true,
                'message' => 'Sincronizzazione completata.',
                'added_files' => $addedFiles,
                'total_files_scanned' => $totalFiles,
                'processed_count' => $processedCount
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



            // Upload to Google Drive
            $client = new \Google_Client();
            $client->setClientId(env('GOOGLE_DRIVE_CLIENT_ID'));
            $client->setClientSecret(env('GOOGLE_DRIVE_CLIENT_SECRET'));
            $client->refreshToken(env('GOOGLE_DRIVE_REFRESH_TOKEN'));
            $service = new \Google_Service_Drive($client);

            $id_folder_master = "1OWWv1lv28wsv3wJsIzqAeg4VQ4r1e8Fe"; // cartella master statica

            //$id_folder_master = "1GrLWfZ4c_ivMXwxWFMG3R86opCO6ftkw"; // cartella master_new per test statica
            $googleFile = new \Google_Service_Drive_DriveFile([
                'name' => $filenameWithoutExtension, // Carica senza estensione .doc
                'parents' => [$id_folder_master],
                'mimeType' => 'application/vnd.google-apps.document'
            ]);

            $createdFile = $service->files->create($googleFile, ['uploadType' => 'media', 'data' => File::get($localFilePath)]);

            // Update the database record
            tbl_master::where('real_name', $filenameWithoutExtension)
                      ->where('id_doc', 'like', 'local_file_%')
                      ->update(['id_doc' => $createdFile->id]);

            return response()->json(['success' => true, 'message' => "File $filename caricato con successo.", 'new_id' => $createdFile->id]);

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