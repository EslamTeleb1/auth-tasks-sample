<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\QueryController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

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


            if ($existingXml->count() >= $splitSize) {
                $disableListener = true;
            }
        } catch (\Exception $e) {
            Log::error('Error capturing query:', ['message' => $e->getMessage()]);
            $disableListener = true;
        }
    }
});

