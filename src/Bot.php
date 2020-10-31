<?php

namespace Botify;

use Botify\Trait\Router;
use Botify\Trait\Telegram;
use Botify\Util\Singleton;
use Botify\Util\Collection;

class Bot extends Singleton
{
    use Router;
    use Telegram;

    private $token;
    private $config = [];
    private $update = false;

    public function __construct($token, $config)
    {
        $this->token = $token;
        $this->config = array_merge($this->config, $config);
    }

    private function initVars()
    {
        $input = file_get_contents('php://input');
        if ($input) {
            $this->update = new Collection(json_decode($input, true)
        }
    }

    public function isUpdate()
    {
        return $this->update !== false;
    }

    public function getUpdate()
    {
        return $this->update;
    }
}
