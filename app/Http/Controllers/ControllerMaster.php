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
        ////
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

	public function open_doc($fileId) {
        $client = new \Google_Client();
        $client->setClientId(env('GOOGLE_DRIVE_CLIENT_ID'));
        $client->setClientSecret(env('GOOGLE_DRIVE_CLIENT_SECRET'));
        $client->refreshToken(env('GOOGLE_DRIVE_REFRESH_TOKEN'));
        $service = new \Google_Service_Drive($client);
		return $service;
	}    

	public function elenco_master() {
       
        $elenco_master=tbl_master::from('tbl_master as m')
        ->select('m.id','m.id_doc','m.real_name','m.rev','m.data_rev','m.created_at','m.updated_at')
        ->where('m.dele','=',0)
->where('m.id','>',4000)
        ->get(); 
		return view('all_views/master/elenco_master',compact('elenco_master'));
    }

    public function dele_master(Request $request) {
        $id_ref=$request->input('id_ref');
        $dele=tbl_master::where('id','=',$id_ref)->update(['dele' =>1]);
        $esito['header']="OK";
        $esito['dele']=$dele;
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


}	