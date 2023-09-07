<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;

class FileTransferController extends Controller
{
    public function checkInternetConnection()
    {
        if ($this->check_internet_connection()) {
            $files = Storage::files('xml_files'); // Change the folder path as needed
            $remoteServerUrl = 'https://example.com/upload'; // Replace with your remote server URL

            foreach ($files as $file) {
                $response = Http::attach('file', file_get_contents(storage_path('app/' . $file)), $file)
                    ->post($remoteServerUrl);

                // Handle the response as needed
            }

            return "Files sent to remote server successfully.";
        } else {
            return "No internet connection.";
        }
    }

    function check_internet_connection()
        {
            exec('ping -c 1 google.com', $output, $return);

            return $return === 0;
        }

}
