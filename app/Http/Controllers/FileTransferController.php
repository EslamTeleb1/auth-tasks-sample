<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
class FileTransferController extends Controller
{
    public function showSendFiles()
    {
        return view('send-files');
    }
    public function sendFilesToRemoteServer()
    {
        // dd("rr");
        if(true)
        {
            $files = Storage::files('encrypt_sql_xml'); // Change the folder path as needed
            $remoteServerUrl = 'http://127.0.0.1:8005/upload'; // Replace with your remote server URL
            // dd($files);
            foreach ($files as $file) {

                $fileContent = file_get_contents(storage_path('app/' . $file));
                $fileName = basename($file);
                // dd($fileName,$fileContent);
                // Generate the CSRF token
            $csrfToken = csrf_token();
            // dd($csrfToken);
                $response = Http::withToken($csrfToken)
                    ->attach('file', $fileContent, $fileName)
                    // ->attach('_token', $csrfToken)
                    ->post($remoteServerUrl);


                if ($response->successful()) {

                   return $response->body();
                    dd("success");

                } else {
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
