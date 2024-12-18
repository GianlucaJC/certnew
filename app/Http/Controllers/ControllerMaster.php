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
        $elenco_master=array();
        if (strlen($cerca_coa)!=0) {
            $elenco_master=tbl_master::from('tbl_master as m')
            ->select('m.id','m.id_doc','m.id_clone_from','m.real_name','m.obsoleti','m.rev','m.data_rev','m.created_at','m.updated_at')
            ->where('m.dele','=',0)
            ->where('m.obsoleti','<>',1)
            ->where('m.real_name','like',"%$cerca_coa%")    
            ->get(); 
        } 
        
		return view('all_views/master/elenco_master',compact('elenco_master'));
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


}	