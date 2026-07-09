<?php

namespace App\Models;

use App\Enums\ThreadStatus;
use Database\Factories\ThreadFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $subject
 * @property int $created_by
 * @property ThreadStatus $status
 * @property Carbon|null $last_message_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $creator
 * @property-read Collection<int, Message> $messages
 */
#[Fillable(['subject', 'created_by', 'status', 'last_message_at'])]
class Thread extends Model
{
    /** @use HasFactory<ThreadFactory> */
    use HasFactory;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => 'open',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => ThreadStatus::class,
            'last_message_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return HasMany<Message, $this>
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    /**
     * @param  Builder<Thread>  $query
     * @return Builder<Thread>
     */
    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('status', ThreadStatus::Open);
    }

    /**
     * @param  Builder<Thread>  $query
     * @return Builder<Thread>
     */
    public function scopeClosed(Builder $query): Builder
    {
        return $query->where('status', ThreadStatus::Closed);
    }

    /**
     * @param  Builder<Thread>  $query
     * @return Builder<Thread>
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if ($term === null || $term === '') {
            return $query;
        }

        return $query->where('subject', 'like', '%'.$term.'%');
    }
}
