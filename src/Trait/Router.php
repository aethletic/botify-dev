<?php

namespace Botify\Trait;

use Botify\Util\Collection;
use Botify\Util\Helper;
use Illuminate\Support\Arr;

trait Router
{
    public function on($needleKeys, $func)
    {
        if (Arr::hasAny($this->update, is_array($needleKeys) ? $needleKeys : [$needleKeys])) {
            $this->executeFunction($func);
        }

        return $this;
    }

    public function onWithValue($data, $callback)
    {
        foreach ($data as $key => $value) {
            $res = data_get($this->update, $key, false);

            if (!$res) {
                continue;
            }

            $res = is_array($res) ? last($res) : $res;

            // regex
            if (Helper::isRegEx($value)) {
                preg_match($value, $this->text, $matches);
                if (sizeof($matches) > 0) {
                    $this->executeFunction($callback);
                }
                return;
            }

            // diff
            if ($res == $value) {
                call_user_func($callback);
            }
        }
        return $this;
    }

    private function executeFunction($func)
    {
        return call_user_func_array($func, is_string($func) ? [$this] : []);
    }
}
