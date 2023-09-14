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

        if (true) {
            $files = Storage::files('encrypt_sql_xml1');
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
                    return $response->body();
                     $timestamp = date('Y-m-d_H-i-s');


                    $destinationPath = 'public/sended/' . $timestamp.basename($file);

                // $xmlFileName = "queries_" . $timestamp . ".xml";
                    Storage::move($file, $destinationPath);
                } else {
                    // dd( $response->status(),$response->body());
                    Log::error('File upload error: ' . $response->status() . ' - ');

                    return $response->body();
                }
            }

            return "Files sent to remote server successfully.";
        }
    }

    function check_internet_connection()
    {
        exec('ping -c 1 google.com', $output, $return);

        return $return === 0;
    }
}
