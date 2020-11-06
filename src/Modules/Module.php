<?php

namespace Botify\Modules;

use Botify\Bot;

abstract class Module
{
    protected $bot;
    protected $db;
    protected $helper;
    protected $keyboard;

    public function __construct()
    {
        $this->bot = Bot::self();
        $this->db = $this->bot->db;
        $this->keyboard = $this->bot->keyboard;
        $this->helper = $this->bot->helper;
    }
}
