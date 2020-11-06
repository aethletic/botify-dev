<?php

namespace Botify\Traits;

use Botify\Util\Helper;
use Illuminate\Support\Arr;

trait Router
{
    private $middlewares = [];
    private $middlewarePassed = null;

    public function on($data, $func)
    {
        if (!$this->checkMiddleware()) {
            return false;
        }

        if (!$this->checkState()) {
            return false;
        }

        foreach ($data as $key => $value) {
            // обычный on без значения
            if (is_numeric($key) && $this->update()->get($key, false)) {
                return $this->executeFunction($func);
            }

            // on со значением
            if (!$found = $this->update()->get($key)->first()) {
                continue;
            }

            if ($found == $value) {
                return $this->executeFunction($func);
            }

            // regex
            if (Helper::isRegEx($value)) {
                preg_match($value, $found, $matches);
                if (sizeof($matches) > 0) {
                    return $this->executeFunction($func);
                }
            }
        }
    }

    public function hear($messages, $func) {
        if ($this->isMessage || $this->isEditedMessage) {
            if (!$this->isCommand && !$this->isCallback) {
                $data = collect($messages)->mapWithKeys(function ($item) {
                    return ['*.text' => $item];
                })->toArray();
                return $this->on($data, $func);
            }
        }
    }

    public function command($messages, $func) {
        if ($this->isCommand && !$this->isCallback) {
            $data = collect($messages)->mapWithKeys(function ($item) {
                return ['*.text' => $item];
            })->toArray();
            return $this->on($data, $func);
        }
    }

    public function callback($messages, $func) {
        if ($this->isCallback) {
            $data = collect($messages)->mapWithKeys(function ($item) {
                return ['callback_query.data' => $item];
            })->toArray();
            return $this->on($data, $func);
        }
    }

    private function executeFunction($func)
    {
        return call_user_func_array($func, is_string($func) ? [$this] : []);
    }

    public function addMiddleware($name, $func)
    {
        $this->middlewares[$name] = $func;
    }

    public function middleware($names = [])
    {
        $names = is_array($names) ? $names : [$names];
        foreach ($names as $name) {
            if ($this->middlewarePassed === false) {
                continue;
            }

            $next = isset($this->middlewares[$name]) ? call_user_func($this->middlewares[$name]) : false;
            $next = is_bool($next) ? $next : false;
            $this->middlewarePassed = $next;
        }

        return $this;
    }

    private function checkMiddleware()
    {
        if ($this->middlewarePassed === null) {
            return true;
        }

        $next = $this->middlewarePassed;
        $this->middlewarePassed = null;

        return $next;
    }

    public function state($names, $stopWords = false)
    {
        $names = is_array($names) ? $names : [$names];

        if ($stopWords) {
            $stopWords = is_array($stopWords) ? $stopWords : [$stopWords];
        }

        if (in_array($this->state->name, $names)) {
            if ($stopWords && !in_array($this->message->text, $stopWords)) {
                $this->statePassed = false;
            } else {
                $this->statePassed = true;
            }
        } else {
            $this->statePassed = false;
        }

        return $this;
    }

    private function checkState()
    {
        if ($this->statePassed === null) {
            return true;
        }

        $next = $this->statePassed;
        $this->statePassed = null;

        return $next;
    }
}
