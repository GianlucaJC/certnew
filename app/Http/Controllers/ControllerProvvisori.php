<?php
namespace App\Http\Controllers;
use App\Http\Controllers\ControllerEditProvvisori;
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
        ->where('p.stato','=',0)
        ->OrWhere('p.stato','=',1)
        ->get(); 
		return view('all_views/provvisori/elenco_provvisori',compact('elenco_provvisori'));
    }

    public function esclusi() {
        $fp=fopen('doc/codici_no.txt',"r");
        $cod_esclusi="";
        while(!feof($fp)){
            $line = fgets($fp);
            if (strlen($cod_esclusi)>0) $cod_esclusi.=", ";
            $cod_esclusi.="'".trim($line)."'";
        }
        fclose($fp);
        return $cod_esclusi;
    }
    public function elenco_lotti(Request $request) {        
        $cod_esclusi=$this->esclusi();
        $cond=" (`i`.`DBcodice` not in (".$cod_esclusi.")) ";

        $data_lotti=$request->input('data_lotti');
        if (strlen($data_lotti)==0) $data_lotti=date("Y-m-d");
        $elenco_lotti=impegnolotti::from('impegnolotti as i')
        ->leftjoin('cert_provvisori as c','i.DBlotto','c.lotto')
        ->select('i.DBlotto','i.DBcodice','i.DBdata','i.DBprodotto','c.check_master')
        ->where('i.DBdata','=',$data_lotti)
        ->where('i.DBcontrollo','<>','!')
        ->whereRaw($cond)
        ->get();        

        return view('all_views/provvisori/elenco_lotti',compact('elenco_lotti','data_lotti'));
    }

    public function regole_custom() {
        $fp=fopen('doc/regole.txt',"r");
        $indice=0;
        $resp=array();
        while(!feof($fp)){
            $line = fgets($fp);
            $line=str_replace("|","",$line);
            $info=explode(";",$line);
            $resp[$info[0]]=$info[1];
            $indice++;
        }
        fclose($fp);
        return $resp;
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
        $opzioni=0;
        $reg=0;
        // $10 - $11 - $12 - $13 - $18X
        if (substr($cod_search, 0, 1) == "$") {
            if ($pref2 == "10" || $pref2 == "11" || $pref2 == "12" || $pref2 == "13" || $pref2 == "15" || $pref2 == "16" || $pref2 == "18") {
                $reg="100";
                $cod_s=substr($cod_search, 1);
            }   
           
           //$2 - $3 - $4 - $5 - $6 - $79 - $8 - $91 - $92 -
           if ($pref1 == "2" || $pref1 == "3" || $pref1 == "4" || $pref1 == "5" || $pref1 == "6" || $pref2 == "79" || $pref1 == "8" || $pref2 == "91" || $pref2 == "92" || $pref2 == "93") { 
              $reg="200";
              $cod_s = substr($cod_search,1);
              $opzioni=1;
              // ''' evita di prendere il file master con _KIT....non so se vale per tutte queste famiglie....di sicuro si per i 91
            }
        }

         
        //$9
        if (substr($cod_search, 0, 1) == "$") {
            If ($pref1 == "9") {
                $reg="300";
                $cod_s = substr($cod_search, 1);
            }   
        }
        
        //$7 - $95 - '$95XXX-XX
        if (substr($cod_search, 0, 1) == "$") {
            if ($pref1 == "7") {$reg="400";$cod_s = $cod_search;}
            
            if (($pref2 == "95") && substr($cod_search, 6, 1) <> "-") {
                $reg="500";
                $cod_s = substr($cod_search, 1);
            }
            
            if (($pref2 == "95") && (substr($cod_search, 6, 1) == "-" || substr($cod_search, 5, 1) == "-")) {
                $reg="600";
                $cod_s = $cod_search;
            }
        }

            
        //da $9501 a $9511
        if ($cod_search == "$9501" || $cod_search == "$9502" || $cod_search == "$9503" || $cod_search == "$9504" || $cod_search == "$9505" || $cod_search == "$9506" || $cod_search == "$9507" || $cod_search == "$9508" || $cod_search == "$9509" || $cod_search == "$9510" || $cod_search == "$9511") {
            $reg="700";
            $cod_s = substr($cod_search, 1);
        }
        
        //$1 $5
        if (substr($cod_search, 0, 1) == "$") {
            if ($pref1 == "1" || $pref1 == "5") {
                $reg="800";
                $cod_s = substr($cod_search, 1);
            }
        }
        
        //$080 - $086 - $088
        if (substr($cod_search, 0, 1) == "$") {
            if ($pref3 =="080" || $pref3 == "086" || $pref3 == "088") {
                $reg="900";
                $cod_s = substr($cod_search, 2);
            }
        }
        
        //*0
        if (substr($cod_search, 0, 1) == "*") {
            if ($pref1 =="0") {
                $reg="1000";
                $cod_s = substr($cod_search, 1);
            } 
        }
        
        //6-7-8-9
        if (substr($cod_search, 0, 1) == "6" || substr($cod_search, 0, 1) == "7" || substr($cod_search, 0, 1) == "8" || substr($cod_search, 0, 1) == "9") {
            $reg="1100";
            $cod_s = $cod_search;
        //91-96
            if ((substr($cod_search, 0, 2) == "91") && substr($cod_search, 0, 1) !="$") {
                $reg="1200";
                $cod_s = $cod_search."_KIT";
            }
        }

            
        //...cominciano con $70
        if (substr($cod_search, 0, 3) == "$70") {
            $reg="1300";
            $cod_s = substr($cod_search, 1);
        }
        
        //91 cerco con kit
        //$XXX
        if (strlen($cod_search) == 4 && substr($cod_search, 0, 1) == "$") {
            $reg="1400";
            $cod_s = $cod_search;
        } 
        //$92XXX-XX : non hanno certificato....poco male....

        $regole_custom=$this->regole_custom();
        if (array_key_exists($cod_search,$regole_custom)) $cod_s=$regole_custom[$cod_search];

        $ris['regola_iniziale']=$reg;
        $master=$this->verifica_stato($cod_s,$opzioni);
        $ris['master']=$master;

        return $ris;

    }
    
    public function DetAlpha($str) {
        $ret = false;
        for ($x=1;$x<strlen($str);$x++) {
            $temp=strtoupper(substr($str, $x, 1));
            if (ord($temp)>64 && ord($temp) < 91) {
                $ret = true;
                break;
            }
        }
        return $ret;
    }

    public function verifica_stato($cod_s, $opzioni) {

        $verifica_stato = "?";
        
        $no_sist = "$71630-19;$71630-20;$71640-19;$71640-20;$71678-01A;$71716-25;$71822-25;$71740-10";
        $cod1 = explode(";",$no_sist);
        $t_sist = 0;
        for ($sca=0;$sca<count($cod1);$sca++) {
            $nos = $cod1[$sca];
            if ($cod_s == $nos) {$t_sist = 1;break;}
        }


        if ($t_sist == 0) {
            $eee = 0;
            if (substr($cod_s, 0, 7) == "$72592C" && substr_count($cod_s, "-") > 0) $eee = 1;

            if ((substr_count($cod_s, "-") > 0 && substr($cod_s, 0, 2) == "$7" || (substr($cod_s, 0, 3) == "$95" && substr_count($cod_s, "-") > 0) && $this->DetAlpha($cod_s) == false) || $eee == 1) {
                //verifica sistemi
                $cod1 = explode("-",$cod_s);
                $cod_s1 = $cod1[0]."P";
                  
                $check_master=tbl_master::select('real_name')->where('real_name','=',$cod_s1)->first();
                if (isset($check_master->real_name)) {
                    $verifica_stato=$check_master->real_name;
                    return $verifica_stato;
                }
            }
        }
        $fl=0;$dato="";$ris_under=array();
        if (substr_count($cod_s, "_")==0) {
            $check_master=tbl_master::select('real_name')
                ->where('real_name','like',"%$cod_s%")
                ->where('real_name','like',"%_%")
                ->where('real_name','not like',"%stampa%");
            if ($check_master->count()>0) {
                $ris_under=$check_master->get();
                $fl=1;
            }
        }

        $trov = 0;
        if ($fl == 1) {
            //ricerca codice dal nome file (array derivato da split _ o -)
            foreach ($ris_under as $under) {
                $dato=$under->real_name;
                $info=explode("_",$dato);
                
                if ($opzioni == 1 && substr_count($dato, "kit") > 0) continue;

                for ($ric=0;$ric<count($info);$ric++) {
                    $cod_doc = $info[$ric];
                    $cod_doc = trim($cod_doc);
                    if ($cod_s == $cod_doc) {
                        $trov = 1;$verifica_stato=$dato;break;
                    }
                }
            }
        }    
        else {
            //codice singolo su nome file
            $check_master=tbl_master::select('real_name')->where('real_name','=',$cod_s)->first();
            if (isset($check_master->real_name)) {$trov=1;$verifica_stato=$check_master->real_name;}
        }
       
        return $verifica_stato;

        /*
            //Di seguito, il codice originale in vb6 che ho codificato in php in questa function

            verifica_stato = "?"
            
            no_sist = "$71630-19;$71630-20;$71640-19;$71640-20;$71678-01A;$71716-25;$71822-25;$71740-10"
            cod1 = Split(no_sist, ";")
            t_sist = 0
            For sca = LBound(cod1) To UBound(cod1)
                nos = cod1(sca)
                If (cod_s = nos) Then t_sist = 1: Exit For
            Next

            If t_sist = 0 Then
                    eee = 0
                    If Mid(cod_s, 1, 7) = "$72592C" And InStr(cod_s, "-") > 0 Then eee = 1
        
                    If ((InStr(cod_s, "-") > 0 And Mid(cod_s, 1, 2) = "$7" Or (Mid(cod_s, 1, 3) = "$95" And InStr(cod_s, "-") > 0)) And DetAlpha(cod_s) = False) Or eee = 1 Then
                    ''verifica sistemi
                            cod1 = Split(cod_s, "-")
                            cod_s = cod1(0)
                            For sca = 0 To File1.ListCount - 1
                            n_file = File1.List(sca)
                            dato = UCase(File1.List(sca))
                                            
                            dato = Replace(dato, ".DOC", "")
                            dato = Replace(dato, ".ODT", "")
                            dato = Replace(dato, " ", "")
                            
                            If cod_s + "P" = dato Then trov = 1: Exit For
                            Next
                            If trov = 1 Then verifica_stato = n_file
                            Exit Function
                    End If
            End If

            
            For sca = 0 To File1.ListCount - 1
                
                n_file = File1.List(sca)
                dato = UCase(File1.List(sca))
                
                dato = Replace(dato, ".DOC", "")
                dato = Replace(dato, ".ODT", "")
                fl = 0
                If (InStr(dato, "_") > 0 And InStr(cod_s, "_") = 0) Then
                    info = Split(dato, "_")
                    fl = 1
                Else
                    'If (InStr(dato, "-")) > 0 Then
                    '    info = Split(dato, "-")
                    '    fl = 1
                    'End If
                End If
                
                entra = 1
                '''utilizzare il flag entra per creare regole in base al parametro opzioni
                If opzioni = 1 And InStr(dato, "KIT") > 0 Then entra = 0
                
                If entra = 1 Then
                        trov = 0
                        If fl = 1 Then
                            
                            '''ricerca codice dal nome file (array derivato da split _ o -)
                            If InStr(dato, "stampa") = 0 And InStr(dato, "STAMPA") = 0 Then
                                For ric = LBound(info) To UBound(info)
                                    cod_doc = info(ric)
                                    cod_doc = Trim(cod_doc)
                                    If cod_s = cod_doc Then trov = 1: Exit For
                                Next
                            End If
                            
                        Else
                        ''' codice singolo su nome file
                            cod_doc = dato
                            cod_doc = Trim(cod_doc)
                            If cod_s = cod_doc Then trov = 1
                        End If
                        
                        If trov = 1 Then verifica_stato = n_file
                        '''se del file OO esiste il corrispondente in DOC prendo quest'ultimo senza fare successivamente altre trasformazioni
                        If trov = 1 Then
                        If (InStr(n_file, "odt") > 0 Or InStr(n_file, "ODT") > 0) Then
                            file_doc = Replace(n_file, "odt", "doc")
                            
                            
                            If Len(Trim(Dir(Percorso + "\master\" + file_doc))) > 0 Then verifica_stato = file_doc
                        End If
                        End If
            End If
            Next
        

        */
    
    }


    public function check_sistema($cod_search) {
        $cod=strtoupper($cod_search);
        return $cod;
        
        /*15.11.2024: ho iniziato a tradurre il codice presente in griglia() di VB6
            ma poi ho deciso di soprassedere: questa function riduce a sistema tutti i codici che possono 
            essere accorpati con determinate regole ma il concetto è diverso in vb6.
            Questo perchè il vecchio prg predisponeva comunque una tabella di tutti i provvisori a prescindere se venivano assegnati o meno i master. Quindi veniva fatta una scansione di tutta la tabella per mostrare nella griglia i sistemi accorpati in modo adeguato. Ma nel nuovo, la tabella viene popolata solo quando i provvisiori vengono assegnati.
        */
        $sistema = "?"; $flag = 0;
        $dx_min = "9999";$dx_max = "00000";
        $lotto_sx = "";$lotto_dx = "";
        $id_s = "";
        
        
        $no_sist = "$71630-19;$71630-20;$71640-19;$71640-20;$71678-01A;$71716-25;$71822-25;$71740-10";
        $cod1 = explode(";",$no_sist);
        $t_sist = 0;
        for ($sca=0;$sca<count($cod1);$sca++) {
            $nos = $cod1[$sca];
            if ($cod = $nos) {$t_sist = 1; break;}
        }
        
       /*
        If in_array(cod, codici_no) = 0 And t_sist = 0 Then ''controllo codici da escludere
            eee = 0
            If Mid(cod, 1, 7) = "$72592C" And InStr(cod, "-") > 0 Then eee = 1
            If (((Mid(cod, 1, 2) = "$7" And InStr(cod, "-") > 0 Or (Mid(cod, 1, 3) = "$95" And InStr(cod, "-") > 0)) And DetAlpha(cod) = False)) Or eee = 1 Then '''se si tratta di un sistema
                sist = Split(cod, "-")
                sx = sist(0)
                dx = sist(1)
                

                
                
                If sistema <> sx Or sistema = "?" Then
                    If sistema <> "?" Then
                        riep = sistema + "-" + dx_min + "-" + dx_max
                        n_add = n_add + 1
                        GX.Rows = GX.Rows + 1
                        sc_r = GX.Rows - 1
                        GX.TextMatrix(sc_r, 0) = id_s
                        
                        
                        GX.TextMatrix(sc_r, 1) = riep
                        GX.TextMatrix(sc_r, 2) = Trim(descr)
                        
                        lotto = lotto_sx + "-" + lotto_dx
                        GX.TextMatrix(sc_r, 3) = lotto
                        
                        GX.TextMatrix(sc_r, 4) = datap
                        GX.TextMatrix(sc_r, 5) = scad
                    End If
                    '''prima volta
                    dx_min = "9999": dx_max = "00000"
                    If dx < dx_min Then
                        dx_min = dx
                        If Not IsNull(rs_db.Fields("DBlotto")) Then lotto_sx = rs_db.Fields("DBlotto")
                    End If
                    If dx > dx_max Then
                        dx_max = dx
                        If Not IsNull(rs_db.Fields("DBlotto")) Then lotto_dx = rs_db.Fields("DBlotto")
                    End If
                    id_s = ID + ";"
                    
                Else
                    '''calcolo riepilogo
                    If t_sist = 0 Then
                            flag = 1
                            If dx < dx_min Then
                                dx_min = dx
                                If Not IsNull(rs_db.Fields("DBlotto")) Then lotto_sx = rs_db.Fields("DBlotto")
                            End If
                            If dx > dx_max Then
                                dx_max = dx
                                If Not IsNull(rs_db.Fields("DBlotto")) Then lotto_dx = rs_db.Fields("DBlotto")
                            End If
                            id_s = id_s + ID + ";"
                            descr = rs_db.Fields("DBprodotto")
                            If Not IsNull(rs_db.Fields("DBdata")) Then datap = rs_db.Fields("DBdata") Else datap = ""
                            
                            If Not IsNull(rs_db.Fields("DBscadenza")) Then
                                scad = CDate(rs_db.Fields("DBscadenza"))
                                If scad = "01/01/1980" Then scad = ""
                            Else
                                scad = ""
                            End If
                End If
                    
                End If
            sistema = sx
            End If
        End If
    */
       
    }

    public function crea_provv(Request $request) {
        $cod_search=$request->input('codice');
        $lotto=$request->input('lotto');
        $n_p=$request->input('n_p');
        $data=date("Y-m-d");
        $check_pres=cert_provvisori::select('id','lotto','check_master','id_doc')->where('lotto','=',$lotto)->first();
        $resp=array();
        
        $file_id=""; $delete=null;
        $ckm=0;$pres=0;
        if (!isset($check_pres->lotto))
            $cert= new cert_provvisori;
        else {
            $pres=1;
            $ckm=$check_pres->check_master;
            $file_id=$check_pres->id_doc;
            $cert = cert_provvisori::find($check_pres->id);
        } 

        $this->check_sistema($cod_search); //da implementare (ora non accorpa i sistemi!)
        $info_master=$this->master_to_provv($cod_search);
        $regola_iniziale=$info_master['regola_iniziale'];
        $codice_associato_master=$info_master['master'];
            //in google drive i file con $ iniziale vengono sostituti con _, però mi serviva per lo Storage
            //$provvisorio=str_replace("$","_",$codice_associato_master);
        
        $provvisorio=$codice_associato_master.".doc";


        //ATTENZIONE!!!!!! Codice statico da rimuovere
        //$codice_associato_master="92114_921140_921141";
        $canc=0;$file_canc="";
        $id_master="?";
        $check_master=tbl_master::select('id_doc')->where('real_name','=',$codice_associato_master)->first();
        if (isset($check_master->id_doc)) $id_master=$check_master->id_doc;
    
        if ($n_p=="1" || $pres==0 || ($pres==1 && $ckm==0)) {
            $ckm=0;

            if ($n_p==1 && strlen($file_id)!=0) {
                $canc=1;$file_canc=$file_id;
            } 
           
            if ($id_master!="?") {
                $file_id=$this->clonemaster($id_master,$lotto);
                //compilazione automatica lotto, scadenza, etc.
                $info_lotto=$this->info_lotto($lotto);
                ControllerEditProvvisori::set_fill($file_id,$info_lotto);
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
        
        if ($id_master!="?") {
            $cert->lotto=$lotto;
            $cert->codice=$cod_search;
            $cert->codice_associato_master=$codice_associato_master;
            $cert->id_doc=$file_id;
            $cert->real_name=$provvisorio;
            $cert->check_master=$ckm;
            $cert->save();
        }
        if ($canc==1) {
            $delete=$this->delete_file_drive($file_canc);
            $resp['delete']=$delete;
        } 

        
        $resp['lotto']=$lotto;
        $resp['regola_iniziale']=$regola_iniziale;
        $resp['codice_associato_master']=$codice_associato_master;
        $resp['provvisorio']=$provvisorio;
        $resp['file_id']=$file_id;
        $resp['check_master']=$ckm;
        $resp['lotto_in_provvisori']=$pres;
        echo json_encode($resp);
    }

    public function info_lotto($lotto){
        $info=impegnolotti::from('impegnolotti as i')
        ->select('i.DBdata','i.DBscadenza')
        ->where('i.DBlotto','=',$lotto)
        ->where('i.DBcontrollo','<>','!')
        ->first();
        $data="";$scadenza="";
        if (isset($info->DBdata)) {
            $data=$info->DBdata;
            $scadenza=$info->DBscadenza;
            $scadenza=str_replace("/",".",$scadenza);
            $scadenza=str_replace("-",".",$scadenza);
        }
        $resp['lt']=$lotto;
        $resp['pdate']=$data;
        $resp['exp']=$scadenza;

        return $resp;


    }

    public function delete_file_drive($id) {
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