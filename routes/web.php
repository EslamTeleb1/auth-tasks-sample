<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\QueryController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\Event;
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


// Event::listen(QueryExecuted::class, function ($query) {
//     Log::info("QueryExecuted event fired.");
// });
$disableListener = false;

Event::listen(QueryExecuted::class, function ($query) use (&$disableListener) {
    if (!$disableListener) {
        try {
            $sql = $query->sql;
            $bindings = $query->bindings;
            $time = $query->time;

            // Replace placeholders in SQL with actual values
            $fullSqlStatement = vsprintf(str_replace('?', "'%s'", $sql), $bindings);

            // Define the splitting criteria (e.g., split every 100 queries)
            $splitSize = 500;

            // Get the current XML file number (if it exists)
            $xmlFileNumber = 1;
            $xmlFileName = "queries_$xmlFileNumber.xml";

            while (Storage::disk('local')->exists($xmlFileName)) {
                $xmlFileNumber++;
                $xmlFileName = "queries_$xmlFileNumber.xml";
            }

            // Check if the XML file already exists
            if (Storage::disk('local')->exists($xmlFileName)) {
                // Read the existing XML file
                $existingXml = simplexml_load_string(Storage::disk('local')->get($xmlFileName));
            } else {
                // Create a new XML file if it doesn't exist
                $existingXml = new SimpleXMLElement('<queries></queries>');
            }

            // Create a new query element
            $queryElement = $existingXml->addChild('query');
            $queryElement->addChild('sql', htmlspecialchars($fullSqlStatement));
            $queryElement->addChild('time', $time);

            // Save the updated XML data back to the file
            Storage::disk('local')->put($xmlFileName, $existingXml->asXML());

            // Check if it's time to disable the listener
            if ($existingXml->count() >= $splitSize) {
                $disableListener = true;
            }
        } catch (\Exception $e) {
            Log::error('Error capturing query:', ['message' => $e->getMessage()]);
            $disableListener = true;
        }
    }
});

