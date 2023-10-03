<?php

namespace Elhareth\LaravelEloquentMetable;

use Elhareth\LaravelEloquentMetable\Exceptions\InvalidDataTypeException;

use Illuminate\Support\Enumerable;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class MetaValueCast implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return mixed
     * @throws Exceptions\InvalidDataTypeException
     */
    public function get($model, string $key, $value, array $attributes)
    {
        $serializedStarts = [
            'a:',
            'b:',
            'd:',
            'i:',
            's:',
            'o:',
            'O:',
        ];


        if (str($value)->startsWith($serializedStarts)) {
            try {
                if ($value = @unserialize($value)) {
                    if (is_array($value)) {
                        return collect($value);
                    } else {
                        return $value;
                    }
                }
            } catch (InvalidDataTypeException $e) {
                //
            }
        }

        return $value;
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return mixed
     */
    public function set($model, string $key, $value, array $attributes)
    {
        if ($value instanceof Enumerable) {
            return serialize($value->all());
        } elseif (is_string($value)) {
            return $value;
        } else {
            return serialize($value);
        }
    }
}
