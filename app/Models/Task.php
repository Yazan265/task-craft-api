<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Comment;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Task extends Model
{
    use HasFactory;
    
    protected $fillable = ['title', 'description', 'project_id', 'assigned_to','status'];
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
}
