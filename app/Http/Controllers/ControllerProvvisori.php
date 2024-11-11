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

//info per usare DRIVE
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
*/


//ref per creazione progetto laravel con riferimento Google Drive
//https://www.luckymedia.dev/blog/laravel-project-setup-for-google-drive-api-integration-part-2


//DOCS
/*	N.B. ho utilizzato DB su porta 8012
    da Console API Google (con una utenza loggata ovviamente)
    (N.B.:la console API deve essere quella dove dovranno essere presenti i documenti)
    https://console.cloud.google.com/apis/dashboard
    
    - abilitare una api (in questo caso google docs api e drive)
        --al servizio bisogna associare una credenziale
    - quindi su credenziali	creare un account di servizio
        --creare una chiave: sarà creato un json da scaricare e
        da deplyoare nel progetto e da settare in setAuthConfig('credentials.json'):
        nel file c'è indicato l'utenza creata ("client_email").
        In Google Docs, condividere il documento ed assegnare l'account di servizio creato ("client_email")
        
    //per usare pdf da laravel con composer:
    "require": {
        ...,
        "barryvdh/laravel-dompdf": "^2.0",
        ...
    }
    - su docs (o drive) condividere il documento (folder) e definire come utente condiviso l'utenza di servizio contenuta nel file json (quindi in questo caso da credentials.json "client_email": "account-service-cert@certoauth-439509.iam.gserviceaccount.com",)
*/

class ControllerProvvisori extends Controller
{
    public function __construct(){
    }




	public function elenco_provvisori() {
       
        $elenco_provvisori=cert_provvisori::from('cert_provvisori as p')
        ->select('p.id','p.id_doc','p.lotto','p.codice','p.codice_associato_master','p.stato','p.created_at','p.updated_at')
        ->get(); 
		return view('all_views/provvisori/elenco_provvisori',compact('elenco_provvisori'));
    }

    public function elenco_lotti(Request $request) {
        $data_lotti=$request->input('data_lotti');
        if (strlen($data_lotti)==0) $data_lotti=date("Y-m-d");
        $elenco_lotti=impegnolotti::from('impegnolotti as i')
        ->leftjoin('cert_provvisori as c','i.DBlotto','c.lotto')
        ->select('i.DBlotto','i.DBcodice','i.DBdata','i.DBprodotto','c.check_master')
        ->where('i.DBdata','=',$data_lotti)
        ->get();        
        return view('all_views/provvisori/elenco_lotti',compact('elenco_lotti','data_lotti'));
    }


    //function provvisoria per regole assegnazione master
    public function master_to_provv($cod_search) {
        //ATTENZIONE!!! Cercare solo tra i master non cancellati!

        $cod_search=str_replace("/","-",$cod_search);
        $cod_search=str_replace("\\","-",$cod_search);
        $cod_search=str_replace("MOD","",$cod_search);
        $cod_search=trim($cod_search);
        
        $pref1 = substr($cod_search, 1, 1);
        $pref2 = substr($cod_search, 1, 2);
        $pref3 = substr($cod_search, 1, 3);
        $cod_s=substr($cod_search, 1);
        // $10 - $11 - $12 - $13 - $18X
        if (substr($cod_search, 0, 1) == "$") {
            if ($pref2 == "10" || $pref2 == "11" || $pref2 == "12" || $pref2 == "13" || $pref2 == "15" || $pref2 == "16" || $pref2 == "18")  
                $cod_s=substr($cod_search, 1);
           
           //$2 - $3 - $4 - $5 - $6 - $79 - $8 - $91 - $92 -
           if ($pref1 == "2" || $pref1 == "3" || $pref1 == "4" || $pref1 == "5" || $pref1 == "6" || $pref2 == "79" || $pref1 == "8" || $pref2 == "91" || $pref2 == "92" || $pref2 == "93") { 
              $cod_s = substr($cod_search,1);
              // ''' evita di prendere il file master con _KIT....non so se vale per tutte queste famiglie....di sicuro si per i 91
            }
        }

         /*
            '$9
            If (Mid(cod_search, 1, 1) = "$") Then
                If (pref1 = "9") Then
                    cod_s = Mid(cod_search, 2)
                End If
            End If
            
            '$7 - $95 - '$95XXX-XX
            If (Mid(cod_search, 1, 1) = "$") Then
                If (pref1 = "7") Then
                cod_s = cod_search
                End If
                If (pref2 = "95") And Mid(cod_search, 7, 1) <> "-" Then
                    cod_s = Mid(cod_search, 2)
                End If
                If (pref2 = "95") And (Mid(cod_search, 7, 1) = "-" Or Mid(cod_search, 6, 1) = "-") Then
                    cod_s = cod_search
                End If
            End If
            
            
            '''da $9501 a $9511
            If (cod_search = "$9501" Or cod_search = "$9502" Or cod_search = "$9503" Or cod_search = "$9504" Or cod_search = "$9505" Or cod_search = "$9506" Or cod_search = "$9507" Or cod_search = "$9508" Or cod_search = "$9509" Or cod_search = "$9510" Or cod_search = "$9511") Then
                cod_s = Mid(cod_search, 2)
            End If
            
            '$1 $5
            If (Mid(cod_search, 1, 1) = "$") Then
                If pref1 = "1" Or pref1 = "5" Then
                    cod_s = Mid(cod_search, 2)
                End If
            End If
            
            '$080 - $086 - $088
            If (Mid(cod_search, 1, 1) = "$") Then
                If pref3 = "080" Or pref3 = "086" Or pref3 = "088" Then
                    cod_s = Mid(cod_search, 3)
                End If
            End If
            
            
            '*0
            If (Mid(cod_search, 1, 1) = "*") Then
                If (pref1 = "0") Then
                    cod_s = Mid(cod_search, 2)
                End If
            End If
            
            '6-7-8-9
            If (Mid(cod_search, 1, 1) = "6" Or Mid(cod_search, 1, 1) = "7" Or Mid(cod_search, 1, 1) = "8" Or Mid(cod_search, 1, 1) = "9") Then
                cod_s = cod_search
            '91-96
                If (Mid(cod_search, 1, 2) = "91") And Mid(cod_search, 1, 1) <> "$" Then
                    cod_s = cod_search + "_KIT"
                End If
            End If
            
            
            '...cominciano con $70
            If (Mid(cod_search, 1, 3) = "$70") Then
                cod_s = Mid(cod_search, 2)
            End If
            
            
            '91 cerco con kit
            
            
            ''' $XXX
            If (Len(cod_search) = 4 And Mid(cod_search, 1, 1) = "$") Then
            cod_s = cod_search
            End If
            '$92XXX-XX : non hanno certificato....poco male....
            
            
                    
            '''regole definite da utente
            
            regola = Percorso + "\regole.txt"
            If Len(Dir(regola)) = 0 Then
                Open Percorso + "\regole.txt" For Output As #1
                Print #1, ""
                Close #1
            End If
            Open regola For Input As #1
            regole = Input(LOF(1), #1)
            Close #1
            regole = Replace(regole, Chr(13), "")
            regole = Replace(regole, Chr(10), "")
            
            
            If InStr(regole, ";") Then
                regol = Split(regole, "|")
                For kkk = LBound(regol) To UBound(regol) - 1
                    reg_att = Split(regol(kkk), ";")
                    reg1 = reg_att(0)
                    reg2 = reg_att(1)
            
                    If UCase(cod_search) = UCase(reg1) And (Len(Trim(cod_search)) = Len(Trim(reg1))) Then
                        cod_s = Replace(cod_search, reg1, reg2, , 1)
                    End If
                    
                    
                    If 1 = 2 Then
                        If UCase(Mid(cod_search, 1, Len(reg1))) = UCase(reg1) Then
                            cod_s = Replace(cod_search, reg1, reg2, , 1)
                        End If
                    End If
                Next
            End If
            
            
            If Len(cod_s) = 0 Then cod_s = "?"
            
            cod_s = UCase(cod_s) 
        */       

        return $cod_s;

    }

    public function crea_provv(Request $request) {
        $cod_search=$request->input('codice');
        $lotto=$request->input('lotto');
        $n_p=$request->input('n_p');
        $data=date("Y-m-d");
        $check_pres=cert_provvisori::select('id','lotto','check_master','id_doc')->where('lotto','=',$lotto)->first();
        $resp=array();
        
        $file_id=null; $delete=null;
        $ckm=0;$pres=0;$file_id_attuale=0;
        if (!isset($check_pres->lotto))
            $cert= new cert_provvisori;
        else {
            $pres=1;
            $ckm=$check_pres->check_master;
            $file_id=$check_pres->id_doc;
            $cert = cert_provvisori::find($check_pres->id);
        } 


        $codice_associato_master=$this->master_to_provv($cod_search);
            //in google drive i file con $ iniziale vengono sostituti con _, però mi serviva per lo Storage
            //$provvisorio=str_replace("$","_",$codice_associato_master);
        
        $provvisorio=$codice_associato_master.".doc";


//ATTENZIONE!!!!!! Codice statico da rimuovere
$codice_associato_master="92114_921140_921141";

        $id_master="?";
        $check_master=tbl_master::select('id_doc')->where('real_name','=',$codice_associato_master)->first();
        if (isset($check_master->id_doc)) $id_master=$check_master->id_doc;
        


        if ($n_p=="1" || $pres==0 || ($pres==1 && $ckm==0)) {
            $ckm=0;

            if ($n_p==1 && $file_id!=0) {
                $delete=$this->delete_file_drive($file_id);
                $resp['delete']=$delete;
            } 
           
            if ($id_master!="?") {
                $file_id=$this->clonemaster($id_master,$lotto);
                $ckm=1;
            }
            //non ho usato lo Storage di google perchè è lento, vedi alternativa nella function clonemaster()
            /*
            if (Storage::disk('google')->exists("master/".$provvisorio)) {
                Storage::disk('google')->copy("master/".$provvisorio, "provvisori/$provvisorio");
                $ckm=1;
            }
            */
        }
     
        $cert->lotto=$lotto;
        $cert->codice=$cod_search;
        $cert->codice_associato_master=$codice_associato_master;
        $cert->id_doc=$file_id;
        $cert->real_name=$provvisorio;
        $cert->check_master=$ckm;
        $cert->save();
     
        
        $resp['lotto']=$lotto;

        $resp['provvisorio']=$provvisorio;
        $resp['file_id']=$file_id;
        $resp['check_master']=$ckm;
        $resp['lotto_in_provvisori']=$pres;
        echo json_encode($resp);
    }

    function delete_file_drive($id) {
        if ($id==null || strlen($id)==0) return false;
        $resp="OK";
        try {
            $client = new \Google_Client();
            $client->setClientId(env('GOOGLE_DRIVE_CLIENT_ID'));
            $client->setClientSecret(env('GOOGLE_DRIVE_CLIENT_SECRET'));
            $client->refreshToken(env('GOOGLE_DRIVE_REFRESH_TOKEN'));
            $service = new \Google_Service_Drive($client);
            $service->files->delete($id);
        }   
        //catch exception
        catch(Exception $e) {
            $resp="KO: Message: ".$e->getMessage();
        }        
        return $resp;
    }
    function clonemaster($id_master,$lotto)
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
        
        $filename_doc=$lotto;
        $id_folder_provvisori="1MK1rIMhajcmOdZGR15h2AG2rs6UzTh2W"; //cartella provvisori statica
        $googleServiceDriveFile = new \Google_Service_Drive_DriveFile([
            'name' => $filename_doc,
            'parents' => [$id_folder_provvisori]
        ]);

        $fileId = $service->files->copy($id_master, $googleServiceDriveFile, ['fields' => 'id']);
        return $fileId->id;
        
    }    

  

}	