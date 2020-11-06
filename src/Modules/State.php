<?php

namespace Botify\Modules;

class State extends Module
{
    public $name = null;
    public $data = null;

    public function __construct()
    {
        parent::__construct();

        if ($state = $this->get()) {
            $this->name = $state['state_name'];
            $this->data = $state['state_data'];
        }
    }

    public function get()
    {
        return isset($this->bot->from->id) ? $this->getById($this->bot->from->id) : false;
    }

    public function getById($userId)
    {
        return $this->db
                    ->table('users')
                    ->select('state_name', 'state_data')
                    ->where('user_id', $userId)
                    ->first();
    }

    public function set($name = null, $data = null)
    {
        $this->setById($this->bot->from->id, $name, $data);
        $this->name = $name;
        $this->data = $data;
    }

    public function setById($userId, $name = null, $data = null)
    {
        return $this->db
                    ->table('users')
                    ->where('user_id', $userId)
                    ->update([
                        'state_name' => $name,
                        'state_data' => $data,
                    ]);
    }

    public function clear()
    {
        $this->clearById($this->bot->from->id);
        $this->name = null;
        $this->data = null;
    }

    public function clearById($userId)
    {
        return $this->db
                    ->table('users')
                    ->where('user_id', $userId)
                    ->update([
                        'state_name' => null,
                        'state_data' => null,
                    ]);
    }
}
