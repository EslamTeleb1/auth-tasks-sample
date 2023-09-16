<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;

class FileTransferController extends Controller
{
    public function showSendFiles()
    {
        return view('send-files');
    }
    public function sendFilesToRemoteServer()
    {
        $encryptionKeySerialized = env('ENCRYPTION_KEY'); // Get the serialized encryption key from .env
        $encryptionKey = unserialize(base64_decode($encryptionKeySerialized));

        if ($this->check_internet_connection()) {
            $files = Storage::files('encrypt_sql_xml1');
            // note : it will be the sub domain of the client
            $remoteServerUrl = 'http://127.0.0.1:8005/api/upload';
            // dd($files);
            foreach ($files as $file) {

                $fileContent = file_get_contents(storage_path('app/' . $file));

                $encryptedXml = Crypto::encrypt($fileContent, $encryptionKey);

                // dd($encryptedXml);
                $fileName = basename($file);

                $csrfToken = csrf_token();

                $response = Http::withToken($csrfToken)
                    ->attach('file', $encryptedXml, $fileName)
                    // ->attach('_token', $csrfToken)
                    ->post($remoteServerUrl);

                if ($response->successful()) {
                    //     dd("success");
                    // return $response->body();
                    $timestamp = date('Y-m-d_H-i-s');


                    $destinationPath = 'public/sended/' . $timestamp . basename($file);

                    // $xmlFileName = "queries_" . $timestamp . ".xml";
                    Storage::move($file, $destinationPath);
                } else {
                    // dd( $response->status(),$response->body());
                    Log::error('File upload error: ' . $response->status() . ' - ');

                    return $response->body();
                }
            }


            return "Files sent to remote server successfully.";
        } else {
            return "there is no internet connection";
        }
    }


    function check_internet_connection()
    {
        $url = 'http://www.google.com'; // You can use any reliable website here

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5); // Set a timeout for the connection attempt (adjust as needed)

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        // Check if the HTTP response code is 200 (OK)
        return $httpCode === 200;
    }
}
