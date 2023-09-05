@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Task Details</h2>
        <p><strong>Name:</strong> {{ $task->name }}</p>
        <p><strong>Description:</strong> {{ $task->description }}</p>
        <p><strong>Assigned to:</strong> {{ $task->user->name }}</p>
        <a href="{{ route('tasks.edit', $task->id) }}" class="btn btn-primary">Edit</a>
    </div>
    
@endsection
