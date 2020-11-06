<?php

namespace Botify\Modules;

class Localization extends Module
{
    private $lang;
    private $config;
    private $messages = [];

    public function __construct($lang)
    {
        parent::__construct();

        $this->lang = $lang;
        $this->config = $this->bot->config('localization', false)->toArray();
        $this->add($lang);
    }

    public function add($lang)
    {
        if (!$lang || trim($lang) == '') {
            $lang = $this->config['default_language'];
        }

        $dir = rtrim($this->config['dir'], '/');
        $localization = "{$dir}/{$lang}.php";
        if (file_exists($localization)) {
            $this->messages[$lang] = require $localization;
        } else {
            $localization = "{$dir}/{$this->config['default_language']}.php";
            $this->lang = $this->config['default_language'];
            if (file_exists($localization)) {
                $this->messages[$this->config['default_language']] = require $localization;
            } else {
                $this->messages[$this->config['default_language']] = [];
            }
        }
    }

    public function msg($key, $replace = false)
    {
        if (array_key_exists($key, $this->messages[$this->lang])) {
            $text = $this->messages[$this->lang][$key];
            $text = $replace ? strtr($text, $replace) : $text;
            return $text;
        }
    }
}
