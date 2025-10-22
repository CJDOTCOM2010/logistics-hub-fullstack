<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'conversation_id',
        'sender_id',
        'message_type',
        'content',
        'attachments',
        'status',
        'edited_at',
        'deleted_at',
        'reply_to_id',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'attachments' => 'array',
        'metadata' => 'array',
        'edited_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the conversation this message belongs to.
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    /**
     * Get the sender of the message.
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Get the message this is replying to.
     */
    public function replyTo(): BelongsTo
    {
        return $this->belongsTo(Message::class, 'reply_to_id');
    }

    /**
     * Get the replies to this message.
     */
    public function replies()
    {
        return $this->hasMany(Message::class, 'reply_to_id');
    }

    /**
     * Get the message's type label.
     */
    public function getMessageTypeLabelAttribute(): string
    {
        return str_replace('_', ' ', title_case($this->message_type));
    }

    /**
     * Get the message's status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return str_replace('_', ' ', title_case($this->status));
    }

    /**
     * Check if message is edited.
     */
    public function isEdited(): bool
    {
        return !is_null($this->edited_at);
    }

    /**
     * Check if message is deleted.
     */
    public function isDeleted(): bool
    {
        return !is_null($this->deleted_at);
    }

    /**
     * Mark message as edited.
     */
    public function markAsEdited(): void
    {
        $this->update(['edited_at' => now()]);
    }

    /**
     * Mark message as deleted.
     */
    public function markAsDeleted(): void
    {
        $this->update(['deleted_at' => now()]);
    }

    /**
     * Mark message as read by a user.
     */
    public function markAsReadBy(int $userId): void
    {
        // This would typically use a separate message_reads table
        // For simplicity, we'll just update the status
        if ($this->status === 'sent') {
            $this->update(['status' => 'read']);
        }
    }

    /**
     * Get the formatted content based on message type.
     */
    public function getFormattedContentAttribute(): string
    {
        return match ($this->message_type) {
            'image' => '[Image]',
            'document' => '[Document]',
            'location' => $this->metadata['location_name'] ?? '[Location]',
            'system' => $this->content,
            'file' => '[File: ' . ($this->metadata['filename'] ?? 'Unknown') . ']',
            'audio' => '[Audio Message]',
            'video' => '[Video Message]',
            default => $this->content ?? '',
        };
    }

    /**
     * Get attachment URLs.
     */
    public function getAttachmentUrlsAttribute(): array
    {
        return $this->attachments ?? [];
    }

    /**
     * Scope to get messages by type.
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('message_type', $type);
    }

    /**
     * Scope to get messages by sender.
     */
    public function scopeBySender($query, int $senderId)
    {
        return $query->where('sender_id', $senderId);
    }

    /**
     * Scope to get non-deleted messages.
     */
    public function scopeNotDeleted($query)
    {
        return $query->whereNull('deleted_at');
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Update conversation's last message info when message is created
        static::created(function ($message) {
            $message->conversation->update([
                'last_message_at' => $message->created_at,
                'last_message_by' => $message->sender_id,
            ]);
        });
    }
}