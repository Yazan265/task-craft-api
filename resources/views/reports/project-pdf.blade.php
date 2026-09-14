<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Project Report - {{ $summary['project_name'] }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .section { margin-bottom: 15px; }
        .table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .table th, .table td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        .table th { background-color: #f4f4f4; }
        .badge { font-weight: bold; padding: 2px 5px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>{{ $summary['project_name'] }} - Status Report</h2>
        <p>Owner: {{ $summary['owner']['name'] }} | Generated Date: {{ date('Y-m-d') }}</p>
    </div>

    <div class="section">
        <h3>Metrics</h3>
        <p><strong>Total Tasks:</strong> {{ $summary['metrics']['total_tasks'] }}</p>
        <p><strong>Completed Tasks:</strong> {{ $summary['metrics']['completed_tasks'] }}</p>
        <p><strong>Completion Rate:</strong> {{ $summary['metrics']['completion_percentage'] }}%</p>
    </div>

    <div class="section">
        <h3>Tasks List</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Status</th>
                    <th>Priority</th>
                </tr>
            </thead>
            <tbody>
                @foreach($project->tasks as $task)
                <tr>
                    <td>{{ $task->id }}</td>
                    <td>{{ $task->title }}</td>
                    <td>{{ $task->status }}</td>
                    <td>{{ $task->priority }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>
</html>