<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
//use Spatie\Permission\Traits\HasRoles;

class utenti extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    //,HasRoles;

	protected $table="utenti";
	protected $connection = 'db_user';	
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'userid',
        'passkey',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'passkey',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'passkey' => 'hashed',
    ];

	
    public function getAuthPassword()
    {
      return bcrypt($this->passkey);
    }

    /**
     * Verifica le credenziali tramite API centralizzata.
     *
     * @param string $testUser
     * @param string $testPass
     * @return array
     */
    public static function verifica($testUser, $testPass)
    {
        // Rilevamento ambiente (Locale vs Produzione)
        $whitelist_local = ['127.0.0.1', '::1', 'localhost'];

        if (in_array($_SERVER['SERVER_NAME'] ?? 'localhost', $whitelist_local)) {
            // URL Locale (XAMPP)
            $apiUrl = 'https://localhost:8012/api_user_liof/api_login.php';
        } else {
            // URL Produzione
            $apiUrl = 'http://liojls02.ad.liofilchem.net:8012/api_user_liof/api_login.php';
        }

        $apiSecret = "StringaSegretaMoltoLunga123!";

        // Dati da inviare
        $data = array(
            'api_token' => $apiSecret,
            'username'  => $testUser,
            'password'  => $testPass,
            'app_name'  => 'SOS'
        );

        // Setup cURL
        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json'
        ));

        // Disabilita verifica SSL (necessario in locale con certificati self-signed)
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        // Esecuzione
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        $rows = array();
        // --- ANALISI RISULTATI ---
        if ($curlError) {
            $rows['header']['login'] = "KO";
            $rows['header']['error'] = "ERRORE CURL (Connessione fallita): " . $curlError;
            return $rows;
        }

        // Decodifica JSON
        $json = json_decode($response, true);

        if ($httpCode == 200 && isset($json['success'])) {
            if ($json['success'] === true) {
                $rows['header']['login'] = "OK";
                $rows['operatore'] = $json['operatore'];
                $rows['admin_sos'] = $json['admin_sos'] ?? null;
                $rows['rst_sos'] = $json['rst_sos'] ?? null;
            } else {
                $rows['header']['login'] = "KO";
                $rows['header']['error'] = $json['message'] ?? 'Errore sconosciuto';
            }
        } else {
            $rows['header']['login'] = "KO";
            $rows['header']['error'] = "Il server ha risposto, ma non è un JSON valido o c'è un errore server (500).";
        }

        return $rows;
    }
}
