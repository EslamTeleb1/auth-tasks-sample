<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\FileTransferController;
use App\Http\Controllers\QueryController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\Event;
use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::middleware(['auth'])->group(function () {
    Route::resource('tasks', TaskController::class);
});
Route::get('/', function () {
    return view('welcome');
});



Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::get('/process-xml', [QueryController::class, 'handleXml'])->name('process-xml');

// sending the xml files to the remote server
Route::get('/sendfiles', [FileTransferController::class,'sendFilesToRemoteServer'])->name('sendfiles');

Route::post('/send-files', [FileTransferController::class,'sendFilesToRemoteServer'])->name('send-files');

Route::get('/send-files', [FileTransferController::class,'showSendFiles'])->name('show-send-files');






$disableListener = false;
$queris = [];
Event::listen(QueryExecuted::class, function ($query) use (&$disableListener, &$queris) {

    $encryptionKey = Key::createNewRandomKey();

    // dd($encryptionKey);

    if (!$disableListener) {
        try {
            $sql = $query->sql;
            $bindings = $query->bindings;
            $time = $query->time;

            // Replace placeholders in SQL with actual values
            $actualSql = vsprintf(str_replace('?', "'%s'", $sql), $bindings);

            // Create a string containing the SQL statement with actual values
            $fullSqlStatement = $actualSql . ';';

            // Check if the XML file already exists
            $timestamp = date('Y-m-d_H-i-s');
            $xmlFileName = "queries_" . $timestamp . ".xml"; // Fixed the file name construction

            // Add the current query to the $queris array
            $queris[] = [
                'fullSqlStatement' => $fullSqlStatement,
                'time' => $time
            ];
        //    dd($queris);
            if (count($queris) >=15) {
                // Create a new query element
                $xml = new SimpleXMLElement('<queries></queries>');

                foreach ($queris as $query) {
                    $sql = $query['fullSqlStatement']; // Use array notation to access array elements
                    $time = $query['time']; // Use array notation to access array elements

                    $queryElement = $xml->addChild('query');
                    $queryElement->addChild('sql', htmlspecialchars($sql));
                    $queryElement->addChild('time', $time);
                }

                // Save the updated XML data back to the file
                // echo $xmlFileName;

                $xmlString = $xml->asXML();
                $encryptedXml = Crypto::encrypt($xmlString, $encryptionKey);

            // Decrypt the XML data
                $decryptedXml = Crypto::decrypt($encryptedXml, $encryptionKey);

                $folder = 'encrypt_sql_xml';
                Storage::disk('local')->put( $folder.'/'.$xmlFileName, $encryptedXml);
                $folder = 'decrypt_sql_xml';
                Storage::disk('local')->put( $folder.'/'. $xmlFileName , $decryptedXml);

                // dd($queris);
                // Clear the $queris array after saving
                $queris = [];
            }

            $disableListener = false;
        } catch (\Exception $e) {
            Log::error('Error capturing query:', ['message' => $e->getMessage()]);
            $disableListener = true;
        }
    }
});


