<?php

namespace Botify\Util;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection as LaravelCollection;

class Collection extends LaravelCollection
{
    public function get($key, $default = null)
    {
        return collect(data_get($this->items, $key, $default))->filter();
    }

    public function getFirst($key, $default = null)
    {
        $data = data_get($this->items, $key, $default);
        return is_array($data) ? head($data) : $data;
    }

    public function getLast($key, $default = null)
    {
        $data = data_get($this->items, $key, $default);
        return is_array($data) ? last($data) : $data;
    }
}
