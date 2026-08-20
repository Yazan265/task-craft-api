<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    /**
     * تحديد هل يمكن للمستخدم عرض تفاصيل المشروع ومهامه؟
     * يسمح فقط لصاحب المشروع (Owner) أو أحد أعضائه (Members).
     */
    public function view(User $user, Project $project): bool
    {
        return $user->id === $project->user_id 
            || $project->members()->where('user_id', $user->id)->exists();
    }

    /**
     * تحديد هل يمكن للمستخدم التعديل على المشروع أو إضافة أعضاء جدد؟
     * يسمح فقط لصاحب المشروع.
     */
    public function update(User $user, Project $project): bool
    {
        return $user->id === $project->user_id;
    }

    /**
     * تحديد هل يمكن للمستخدم حذف المشروع؟
     */
    public function delete(User $user, Project $project): bool
    {
        return $user->id === $project->user_id;
    }
}
