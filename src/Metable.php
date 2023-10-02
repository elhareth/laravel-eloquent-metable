<?php

namespace Elhareth\LaravelEloquentMetable;

use Elhareth\LaravelEloquentMetable\DataType\Registry;

use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Model;

/**
 * Model for storing meta data.
 *
 * @property int $id
 * @property string $metable_type
 * @property int $metable_id
 * @property string $type
 * @property string $key
 * @property string $value
 * @property Model $metable
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
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = [];

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
