<?php

namespace App\Listeners;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use SimpleXMLElement;

class QueryExecutedListener
{
    /**
     * Handle the event.
     *
     * @param  QueryExecuted  $event
     * @return void
     */
    public function handle(QueryExecuted $event)
    {
        $disableListener = false;

        if (!$disableListener) {
            try {
                $sql = $event->sql;
                $bindings = $event->bindings;
                $time = $event->time;

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
    }
}
