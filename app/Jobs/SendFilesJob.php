<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;

class SendFilesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $encryptionKeySerialized = env('ENCRYPTION_KEY');
        $encryptionKey = unserialize(base64_decode($encryptionKeySerialized));

        if ($this->check_internet_connection()) {
            $files = Storage::files('encrypt_sql_xml1');
            $remoteServerUrl = 'http://127.0.0.1:8005/api/upload';

            foreach ($files as $file) {
                $fileContent = file_get_contents(storage_path('app/' . $file));
                $encryptedXml = Crypto::encrypt($fileContent, $encryptionKey);
                $fileName = basename($file);

                $csrfToken = csrf_token();

                $response = Http::withToken($csrfToken)
                    ->attach('file', $encryptedXml, $fileName)
                    ->post($remoteServerUrl);

                if ($response->successful()) {
                    $timestamp = date('Y-m-d_H-i-s');
                    $destinationPath = 'public/sended/' . $timestamp . basename($file);
                    Storage::move($file, $destinationPath);
                } else {
                    Log::error('File upload error: ' . $response->status() . ' - ');
                }
            }
        } else {
            Log::error('No internet connection.');
        }
    }
    private function check_internet_connection()
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
