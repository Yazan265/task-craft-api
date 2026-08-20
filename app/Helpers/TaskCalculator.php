<?php

namespace App\Helpers;

class TaskCalculator
{
    /**
     * حساب نسبة إنجاز المهام
     */
    public function calculateCompletionPercentage(int $completedTasks, int $totalTasks): float
    {
        if ($totalTasks === 0) {
            return 0.0;
        }

        return round(($completedTasks / $totalTasks) * 100, 2);
    }
}