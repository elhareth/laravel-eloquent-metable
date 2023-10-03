<?php

namespace Elhareth\LaravelEloquentMetable;

use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Model;

/**
 * Model for storing meta data.
 *
 * @property int    $metable_id
 * @property string $metable_type
 * @property string $name
 * @property string $value
 * @property string $group
 * @property Model  $metable
 */
class Metable extends Model
{
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'value',
        'group',
        'metable_id',
        'metable_type',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'value' => MetaValueCast::class,
    ];

    /**
     * Queued Value
     *
     * @var mixed
     */
    protected $queuedValue;

    /**
     * Metable Relation.
     *
     * @return MorphTo
     */
    public function metable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Retrieve the underlying serialized value.
     *
     * @return string
     */
    public function getRawValue(): string
    {
        return $this->attributes['value'];
    }
}
