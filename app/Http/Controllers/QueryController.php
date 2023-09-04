<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use SimpleXMLElement;

class QueryController extends Controller
{
    public function handleXml(Request $request)
    {
        // Retrieve the XML data from the request
        $filePath = storage_path('app/queries.xml');
        $xmlData = file_get_contents($filePath);

        // dd($xmlData);
        // $xml = simplexml_load_string($xmlData);
        // $xmlData = $request->getContent();

        // Parse the XML data into a SimpleXMLElement object
        $xml = simplexml_load_string($xmlData);

        $queries = [];
        foreach ($xml->query as $query) {
            $sql = (string)$query->sql;
            $time = (float)$query->time;

            // Store the SQL query and time in an array
            $queries[] = [
                'sql' => $sql,
                'time' => $time,
            ];

            // Execute the SQL query
          DB::statement($sql);
        }

        // Now you have an array of queries with their respective times
        // You can perform further processing or return a response as needed

        // For example, you can return the queries as JSON
        return response()->json(['queries' => $queries]);
    }
}
