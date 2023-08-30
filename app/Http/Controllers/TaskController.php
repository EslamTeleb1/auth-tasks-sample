<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $user = Auth::user(); // Get the current authenticated user
        $tasks = $user->tasks;
//    dd($tasks);
      // $tasks = Task::all();
        return view('tasks.index', compact('tasks'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('tasks.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|max:255',
            'description' => 'required',
        ]);

        $validatedData['user_id'] = auth()->user()->id;
        $validatedData['status'] = "pending";//default is pending

        // Enable query log
        DB::enableQueryLog();

        // Perform the database operation (for example, creating a task)
        Task::create($validatedData);


        // $this->queryLoger();

        return redirect()->route('tasks.index')->with('success', 'Task created successfully.');
    }


    protected function queryLoger()
    {
            // Get the query log
            $queryLog = DB::getQueryLog();

            // Get the first query from the query log
            $queryInfo = $queryLog[0];

            // Original SQL query with placeholders
            $originalSql = $queryInfo['query'];

            // Binding values
            $bindings = $queryInfo['bindings'];

            // Replace placeholders in the SQL query with binding values
            $modifiedSql = vsprintf(str_replace('?', "'%s'", $originalSql), $bindings);
            DB::statement($modifiedSql);
            // Output the modified SQL query
            dd($modifiedSql);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $task = Task::findOrFail($id);
        return view('tasks.show', compact('task'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $task = Task::findOrFail($id);
        return view('tasks.edit', compact('task'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'name' => 'required|max:255',
            'description' => 'required',
        ]);

        $task = Task::findOrFail($id);
        $task->update($validatedData);

        return redirect()->route('tasks.index')->with('success', 'Task updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $task = Task::findOrFail($id);
        $task->delete();

        return redirect()->route('tasks.index')->with('success', 'Task deleted successfully.');
    }
}
