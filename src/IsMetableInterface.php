<?php

namespace Elhareth\LaravelEloquentMetable;

interface IsMetableInterface
{
    /**
     * Set default metables
     * 
     * @return array
     */
    public function defaultMetables(): array;
}