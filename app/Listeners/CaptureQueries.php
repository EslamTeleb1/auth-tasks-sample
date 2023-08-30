<?php

namespace App\Listeners;

use App\Models\CapturedQuery;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class CaptureQueries
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  QueryExecuted  $event
     * @return void
     */
    public function handle(QueryExecuted $event)
    {
        try {

            $sql = $event->sql;
            $bindings = $event->bindings;
            $time = $event->time;

            CapturedQuery::create([
                'sql' => $sql,
                'bindings' => json_encode($bindings),
                'time' => $time,
            ]);
        } catch (\Exception $e) {
            Log::error('Error capturing query:', ['message' => $e->getMessage()]);
        }
    }
}
