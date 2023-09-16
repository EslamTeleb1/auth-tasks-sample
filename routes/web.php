<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\FileTransferController;
use App\Http\Controllers\QueryController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
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
Route::get('/sendfiles', [FileTransferController::class,'sendFilesToRemoteServer1'])->name('sendfiles');

Route::post('/send-files', [FileTransferController::class,'sendFilesToRemoteServer'])->name('send-files');

Route::get('/send-files', [FileTransferController::class,'showSendFiles'])->name('show-send-files');

$disableListener = false;
$queris = []; // Initialize the array outside of the callback function


// DB::listen(
//     function ($query) use (&$disableListener, &$queris) {
//         $encryptionKeySerialized = env('ENCRYPTION_KEY'); // Get the serialized encryption key from .env
//         $encryptionKey = unserialize(base64_decode($encryptionKeySerialized));
//         DB::enableQueryLog();

//         // and then you can get query log

//         // dd(DB::getQueryLog());
//         if (!$disableListener) {
//             try {
//                 $sql = $query->sql;
//                 $bindings = $query->bindings;
//                 $time = $query->time;

//                 // Replace placeholders in SQL with actual values
//                 $actualSql = vsprintf(str_replace('?', "'%s'", $sql), $bindings);

//                 // Create a string containing the SQL statement with actual values
//                 $fullSqlStatement = $actualSql . ';';

//                 // Add the current query to the $queris array
//                 $queris[] = [
//                     'fullSqlStatement' => $fullSqlStatement,
//                     'time' => $time
//                 ];
//                 // foreach ($queris as $query) {
//                 //     echo $query['fullSqlStatement'];
//                 // }
//                  echo count($queris);
//                 // Check if you have collected enough queries
//                 if (count($queris) == 50) {
//                     // echo count($queris);
//                     // Create a new query element
//                     $xml = new SimpleXMLElement('<queries></queries>');

//                     foreach ($queris as $query) {
//                         $sql = $query['fullSqlStatement'];
//                         $time = $query['time'];

//                         $queryElement = $xml->addChild('query');
//                         $queryElement->addChild('sql', htmlspecialchars($sql));
//                         $queryElement->addChild('time', $time);
//                     }

//                     $xmlString = $xml->asXML();
//                     $encryptedXml = Crypto::encrypt($xmlString, $encryptionKey);

//                     // Decrypt the XML data
//                     $decryptedXml = Crypto::decrypt($encryptedXml, $encryptionKey);

//                     $timestamp = date('Y-m-d_H-i-s');
//                     $xmlFileName = "queries_" . $timestamp . ".xml";

//                     $folder = 'encrypt_sql_xml';
//                     Storage::disk('local')->put($folder . '/' . $xmlFileName, $encryptedXml);
//                     $folder = 'decrypt_sql_xml';
//                     Storage::disk('local')->put($folder . '/' . $xmlFileName, $decryptedXml);

//                     // Clear the $queris array after saving
//                     $queris = [];
//                     // dd($queris);
//                 }

//                 $disableListener = false;
//             } catch (\Exception $e) {
//                 Log::error('Error capturing query:', ['message' => $e->getMessage()]);
//                 $disableListener = true;
//             }
//         }
//     }
// );


Event::listen(QueryExecuted::class,
 function ($query) use (&$disableListener, &$queris) {
    $encryptionKeySerialized = env('ENCRYPTION_KEY'); // Get the serialized encryption key from .env
    $encryptionKey = unserialize(base64_decode($encryptionKeySerialized));
    // DB::enableQueryLog();

    // and then you can get query log
 try {
        // dd(DB::getQueryLog());
        if (!$disableListener) {

                $sql = $query->sql;
                $bindings = $query->bindings;
                $time = $query->time;

                // Replace placeholders in SQL with actual values
                $actualSql = vsprintf(str_replace('?', "'%s'", $sql), $bindings);

                // Create a string containing the SQL statement with actual values
                $fullSqlStatement = $actualSql . ';';

                $xmlFileName = "queries.xml";
                $filePath = storage_path('app/encrypt_sql_xml1/' . $xmlFileName);

                $xml = new SimpleXMLElement('<queries></queries>');

                if (file_exists($filePath)) {
                    // Load the existing XML file
                    $xml = simplexml_load_file($filePath);
                }
                // Add new queries to the XML

                    $sql = $fullSqlStatement;
                    $time = $time;

                    $queryElement = $xml->addChild('query');
                    $queryElement->addChild('sql', htmlspecialchars($sql));
                    $queryElement->addChild('time', $time);

                $xmlString = $xml->asXML();
                // $timestamp = date('Y-m-d_H-i-s');
                // $xmlFileName = "queries_" . $timestamp . ".xml";

                $folder = 'encrypt_sql_xml1';
                Storage::disk('local')->put($folder . '/' . $xmlFileName, $xmlString);

                $disableListener = false;
                }

        }
        catch (\Exception $e) {
            Log::error('Error capturing query:', ['message' => $e->getMessage()]);
            $disableListener = true;
        }

});

