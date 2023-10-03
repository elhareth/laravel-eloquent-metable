<?php

namespace Elhareth\LaravelEloquentMetable;

use ArrayAccess;
use InvalidArgumentException;

use Illuminate\Support\Arr;

use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

trait IsMetable
{
    /**
     * Queued meta records
     *
     * @var array
     */
    protected array $queuedMetables = [];

    /**
     * Register trait boot function
     */
    protected static function bootIsMetable()
    {
        static::created(function (self $model) {
            if (count($model->queuedMetables) === 0) {
                return;
            }

            $model->upsertingMetables($model->queuedMetables);

            $model->queuedMetables = [];
        });

        static::deleted(function (self $model) {
            if (method_exists($model, 'isForceDeleting') && !$model->isForceDeleting()) {
                return;
            }
            $model->deleteMetaRecords();
        });
    }

    /**
     * Filter records which have `Metable` attached to a given name.
     *
     * @param  Builder      $query
     * @param  string|array $name
     * @return void
     */
    public function scopeWhereHasMeta(Builder $query, string|array $name): void
    {
        $query->whereHas('metalist', function (Builder $query) use ($name) {
            $query->whereIn('name', (array)$name);
        });
    }

    /**
     * Filter records which doesnt have `Metable` with a given name.
     *
     * @param  Builder      $query
     * @param  string|array $name
     * @return void
     */
    public function scopeWhereDoesntHaveMeta(Builder $query, string|array $name): void
    {
        $query->whereDoesntHave('metalist', function (Builder $query) use ($name) {
            $query->whereIn('name', (array)$name);
        });
    }

    /**
     * Query with metable value and group
     *
     * @param Builder     $query
     * @param string      $name
     * @param mixed       $value
     * @param string|null $group
     *
     * @return void
     */
    public function scopeWhereMeta(
        Builder $query,
        string $name,
        $value = null,
        string $group = null
    ): void {
        if (!is_string($value)) {
            $value = $this->modelizeMetable($name, $value, $group)->getRawValue();
        }

        $query->whereHas('metalist', function (Builder $query) use ($name, $value, $group) {
            $query->where('name', $name);
            $query->where('value', $value);
            $query->when($group, function (Builder $subquery, string $group) {
                $subquery->where('group', $group);
            });
        });
    }

    /**
     * Query scope to restrict the query to records which have `Metable` with a specific name and a value within a specified set of options.
     *
     * @param  Builder $query
     * @param  string  $name
     * @param  array   $values
     * @return void
     */
    public function scopeWhereMetaIn(Builder $query, string $name, array $values): void
    {
        $values = array_map(function ($val) use ($name) {
            return is_string($val) ? $val : $this->modelizeMetable($name, $val)->getRawValue();
        }, $values);

        $query->whereHas('metalist', function (Builder $query) use ($name, $values) {
            $query->where('name', $name);
            $query->whereIn('value', $values);
        });
    }

    /**
     * Get all of the models metalist.
     *
     * @return MorphMany
     */
    public function metalist(): MorphMany
    {
        return $this->morphMany(Metable::class, 'metable');
    }

    /**
     * Delete all meta attached to the model.
     *
     * @return void
     */
    public function deleteMetaRecords(): void
    {
        $this->metalist()->delete();
    }

    /**
     *
     *
     */
    public function getQueuedMetablesAttribute()
    {
        return $this->queuedMetables;
    }

    /**
     * Set metables attribute
     *
     * @param  array $metables
     * @return void
     */
    public function setMetablesAttribute(array $metables)
    {
        if (!$this->exists) {
            $this->queuedMetables = $metables;
            return;
        }

        $this->upsertingMetables($metables);
    }

    /**
     * Upseting Metable Records
     *
     * @param  array $records null
     * @return int|bool
     */
    public function upsertingMetables(array $records = [])
    {
        $records = $this->refineMetables($records);
        return Metable::upsert(
            $records,
            ['metable_id', 'metable_type', 'name'],
            ['value']
        );
    }

    /**
     * Add or update the value of the `Metable` at a given name.
     *
     * @param  string|array $name  certian meta name or array of meta
     * @param  mixed        $value if $name is array & $group is null $value works as group
     * @param  string|null  $group
     * @return void
     */
    public function setMeta(string|array $name, $value = null, string $group = null)
    {
        $metalist = [];
        if (is_array($name) && !array_is_list($name)) {
            $group = is_null($group) && is_string($value) ? $value : null;
            $metalist = $this->refineMetablesList($name, $group);
        } else {
            $metalist[$name] = [
                'name' => $name,
                'value' => $value,
            ];

            if (!is_null($group)) $metalist[$name]['group'] = $group;

            $metalist[$name]['metable_id'] = $this->getKey();
            $metalist[$name]['metable_type'] = $this->getMorphClass();

        }


        $this->metalist()->upsert($this->refineMetables($metalist), [
            'name',
            'metable_id',
            'metable_type',
        ], ['value']);
    }

    /**
     * Get Meta
     *
     * @param  string $name
     * @param  mixed  $default Fallback value if no Metable is found.
     * @return mixed
     */
    public function getMeta(string $name, $default = null)
    {
        if ($this->hasMeta($name)) {
            return $this->metalist()->where('name', $name)->value;
        }

        return $default;
    }

    /**
     * Check meta existance
     *
     * @param  string $name
     * @return bool
     */
    public function hasMeta(string $name): bool
    {
        return $this->metalist()->where('name', $name)->first() ? true : false;
    }

    /**
     * Delete a `Metable`
     *
     * @param  string $name
     * @return void
     */
    public function deleteMeta(string $name): void
    {
        if ($this->hasMeta($name)) {
            $this->metalist()->where('name', $name)->delete();
        }
    }

    /**
     * Get Meta by group
     * 
     * @param  string $group
     * @return Collection
     */
    public function getMetaGroup(string $group)
    {
        return $this->metalist()->where('group', $group)->get();
    }

    /**
     * Create a new `Metable` record.
     *
     * @param  string      $name
     * @param  mixed       $value
     * @param  string|null $group
     * @return Metable
     */
    protected function modelizeMetable(string $name = '', $value = '', string $group = null): Metable
    {
        $meta = new Metable([
            'name' => $name,
            'value' => $value,
        ]);

        if (!is_null($group)) $meta->group = $group;

        $meta->metable_type = $this->getMorphClass();
        $meta->metable_id = $this->getKey();

        return $meta;
    }

    /**
     * Refine metalist
     *
     * @param  array       $metalist
     * @param  string|null $group
     * @return array
     */
    protected function refineMetables(array $list, string $group = null): array
    {
        $metalist = [];

        foreach ($list as $key => $val) {
            $evalue = is_array($val) && array_key_exists('value', $val) ? $val['value'] : $val;
            $egroup = is_array($val) && array_key_exists('group', $val) ? $val['group'] : $group;
            $metalist[$key] = [
                'name' => $key,
                'value' => $evalue,
                'group' => $egroup,
                'metable_id' => $this->getKey(),
                'metable_type' => $this->getMorphClass(),
            ];

        }

        return $metalist;
    }

    /**
     * Get Default Metable Records
     *
     * @param  array $records
     * @return array
     */
    protected function getDefaultMetables(array $records = []): array
    {
        $metables = collect();
        $defaults = method_exists($this, 'defaultMetables') ? $this->defaultMetables() : [];

        if (count($defaults) > 1) {
            $metables->push($defaults);
            $metables = $metables->merge($records);
        } else {
            $metables->push($records);
        }

        return $metables->toArray();
    }
}
