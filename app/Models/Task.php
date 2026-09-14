<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Comment;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Task extends Model
{
    use HasFactory;
    
    protected $fillable = ['title', 'description', 'project_id', 'assigned_to','status','priority'];
    protected $casts = [
    'status' => 'string', 
        ];
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function assignee()
        {
        
            return $this->belongsTo(User::class, 'assigned_to');
        }
        public function comments(): HasMany
            {
                return $this->hasMany(Comment::class);
            }

            /**
 * Scope لتصفية المهام حسب الحالة (status)
 */
public function scopeFilterByStatus($query, ?string $status)
{
    if ($status) {
        return $query->where('status', $status);
    }

    return $query;
}

/**
 * Scope لتصفية المهام حسب الأولوية (priority)
 */
public function scopeFilterByPriority($query, ?string $priority)
{
    if ($priority) {
        return $query->where('priority', $priority);
    }

    return $query;
}

/**
 * Scope للبحث النصي في العنوان والوصف (search)
 */
    public function scopeSearch($query, ?string $search)
    {
        if ($search) {
            return $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
            });
        }

        return $query;
    }
}
