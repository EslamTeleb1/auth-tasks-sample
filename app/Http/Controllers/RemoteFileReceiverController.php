<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RemoteFileReceiverController extends Controller
{
    public function receiveFiles(Request $request)
    {
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileName = $file->getClientOriginalName();
            $file->storeAs('xml_received', $fileName, 'public'); // Change the storage path as needed

            return "File received and stored on the remote server.";
        } else {
            return "No file uploaded.";
        }
    }
}
