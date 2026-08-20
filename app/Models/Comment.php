<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Storage;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'task_id',
        'content',
        'attachment',
    ];

    protected $appends = ['attachment_url'];

  
    protected function attachmentUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->attachment 
                ? Storage::disk('public')->url($this->attachment) 
                : null,
        );
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function task()
    {
        return $this->belongsTo(Task::class);
    }
}