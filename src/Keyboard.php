<?php

namespace Botify;

use Illuminate\Support\Arr;

class Keyboard
{
    protected static $keyboards = [];

    public function show($keyboard, $one_time = false, $resize = true)
    {
        if (!is_array($keyboard)) {
            $keyboard = self::$keyboards[$keyboard];
        }

        // для дедекта инлайн клавы
        $inlineKeys = [
            '0.0.text', '0.0.callback_data', '0.0.url', '0.0.login_url',
            '0.0.switch_inline_query', '0.0.switch_inline_query_current_chat',
            '0.0.callback_game', '0.0.pay'
        ];

        // inline keyboard
        if (Arr::hasAny($keyboard, $inlineKeys)) {
            return $this->inline($keyboard);
        }

        $markup = [
            'keyboard' => $keyboard,
            'resize_keyboard' => $resize,
            'one_time_keyboard' => $one_time,
        ];

        return json_encode($markup);
    }

    public function hide()
    {
        $markup = [
            'hide_keyboard' => true,
            'selective' => true,
        ];

        return json_encode($markup);
    }

    public function inline($keyboard)
    {
        if (!is_array($keyboard)) {
            $keyboard = self::$keyboards[$keyboard];
        }

        return json_encode(['inline_keyboard' => $keyboard]);
    }

    public function contact($text = 'Contact', $resize = true, $one_time = false)
    {
        $keyboard = [
            [
                'text' => $text,
                'request_contact' => true,
            ]
        ];

        $markup = [
            'keyboard' => [$keyboard],
            'resize_keyboard' => $resize,
            'one_time_keyboard' => $one_time,
        ];

        return json_encode($markup);
    }

    public function location($text = 'Location', $resize = true, $one_time = false)
    {
        $keyboard = [
            [
                'text' => $text,
                'request_location' => true,
            ]
        ];

        $markup = [
            'keyboard' => [$keyboard],
            'resize_keyboard' => $resize,
            'one_time_keyboard' => $one_time,
        ];

        return json_encode($markup);
    }

    public function set($keyboards = [])
    {
        self::$keyboards = $keyboards;
    }

    public function add($keyboards = [])
    {
        self::$keyboards = array_merge(self::$keyboards, $keyboards);
    }

    public function clear()
    {
        self::$keyboards = [];
    }
}
