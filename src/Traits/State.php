<?php

namespace Botify\Modules;

class State
{
    public function get()
    {

    }

    public function getById($userId)
    {
        return $this->db
                    ->table('users')
                    ->select('state_name', 'state_data')
                    ->where('user_id', $userId)
                    ->first();
    }

    public function set($name = false, $value = false)
    {

    }

    public function clear()
    {

    }
}
