<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'conversation_id',
        'type',
        'title',
        'shipment_id',
        'participants',
        'created_by',
        'status',
        'last_message_at',
        'last_message_by',
        'settings',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'participants' => 'array',
        'settings' => 'array',
        'last_message_at' => 'datetime',
    ];

    /**
     * Get the user who created the conversation.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who sent the last message.
     */
    public function lastMessageSender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_message_by');
    }

    /**
     * Get the shipment associated with this conversation.
     */
    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class);
    }

    /**
     * Get the messages in this conversation.
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->orderBy('created_at');
    }

    /**
     * Get the participants in this conversation.
     */
    public function participantUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'conversation_participants')
            ->withPivot('joined_at', 'left_at', 'role', 'is_muted', 'is_archived')
            ->withTimestamps();
    }

    /**
     * Get the latest message in the conversation.
     */
    public function getLatestMessageAttribute(): ?Message
    {
        return $this->messages()->latest()->first();
    }

    /**
     * Get the unread message count for a user.
     */
    public function getUnreadCountForUser(int $userId): int
    {
        return $this->messages()
            ->where('sender_id', '!=', $userId)
            ->whereDoesntHave('reads', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->count();
    }

    /**
     * Check if a user is a participant.
     */
    public function isParticipant(int $userId): bool
    {
        return in_array($userId, $this->participants ?? []);
    }

    /**
     * Add a participant to the conversation.
     */
    public function addParticipant(int $userId, string $role = 'member'): void
    {
        $participants = $this->participants ?? [];
        if (!in_array($userId, $participants)) {
            $participants[] = $userId;
            $this->update(['participants' => $participants]);
        }

        $this->participantUsers()->attach($userId, [
            'role' => $role,
            'joined_at' => now(),
        ]);
    }

    /**
     * Remove a participant from the conversation.
     */
    public function removeParticipant(int $userId): void
    {
        $participants = $this->participants ?? [];
        $participants = array_values(array_diff($participants, [$userId]));
        $this->update(['participants' => $participants]);

        $this->participantUsers()->updateExistingPivot($userId, [
            'left_at' => now(),
        ]);
    }

    /**
     * Get the type label.
     */
    public function getTypeLabelAttribute(): string
    {
        return str_replace('_', ' ', title_case($this->type));
    }

    /**
     * Get the status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return str_replace('_', ' ', title_case($this->status));
    }

    /**
     * Scope to get conversations by type.
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to get conversations for a user.
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->whereJsonContains('participants', $userId);
    }

    /**
     * Scope to get active conversations.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Generate conversation ID on creation
        static::creating(function ($conversation) {
            if (!$conversation->conversation_id) {
                $conversation->conversation_id = 'conv_' . date('YmdHis') . mt_rand(100, 999);
            }
        });
    }
}