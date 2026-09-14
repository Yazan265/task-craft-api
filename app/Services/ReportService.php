<?php

namespace App\Services;

use App\Models\Project;

class ReportService
{
    /**
     * توليد مصفوفة الإحصائيات الشاملة للمشروع
     */
    public function getProjectSummary(Project $project): array
    {
        $tasks = $project->tasks;
        $totalTasks = $tasks->count();
        $completedTasks = $tasks->where('status', 'completed')->count();
        $pendingTasks = $totalTasks - $completedTasks;

        $completionPercentage = $totalTasks > 0 
            ? round(($completedTasks / $totalTasks) * 100, 2) 
            : 0;

        $priorityBreakdown = [
            'high' => $tasks->where('priority', 'high')->count(),
            'medium' => $tasks->where('priority', 'medium')->count(),
            'low' => $tasks->where('priority', 'low')->count(),
        ];

        $statusBreakdown = [
            'pending' => $tasks->where('status', 'pending')->count(),
            'in_progress' => $tasks->where('status', 'in_progress')->count(),
            'completed' => $completedTasks,
        ];

        return [
            'project_id' => $project->id,
            'project_name' => $project->name ?? $project->title,
            'description' => $project->description,
            'owner' => [
                'id' => $project->user?->id,
                'name' => $project->user?->name,
                'email' => $project->user?->email,
            ],
            'metrics' => [
                'total_tasks' => $totalTasks,
                'completed_tasks' => $completedTasks,
                'pending_tasks' => $pendingTasks,
                'completion_percentage' => $completionPercentage,
            ],
            'priority_breakdown' => $priorityBreakdown,
            'status_breakdown' => $statusBreakdown,
            'members_count' => $project->members()->count(),
        ];
    }
}