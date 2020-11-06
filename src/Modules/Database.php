<?php

namespace Botify\Modules;

class Database extends Module
{
    public function __invoke()
    {
        $dbConfig = $this->bot->config('database', false)->toArray();

        if (!$dbConfig || !isset($dbConfig['driver'])) {
            return false;
        }

        $connectionConfig = $dbConfig[$dbConfig['driver']];
        $connectionConfig['driver'] = $dbConfig['driver'];

        $factory = new \Database\Connectors\ConnectionFactory();
        $this->db = $factory->make($connectionConfig);

        if ($this->bot->config('general.auto_create_db_tables', false)->first()) {
            $this->createTables();
        }

        return $this->db;
    }

    public function createTables()
    {
        $user_sql = "CREATE TABLE IF NOT EXISTS `users` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `user_id` bigint(20) NOT NULL,
          `active` tinyint(4) DEFAULT NULL,
          `source` text DEFAULT NULL,
          `first_message` int(11) DEFAULT NULL,
          `last_message` int(11) DEFAULT NULL,
          `fullname` text DEFAULT NULL,
          `firstname` text DEFAULT NULL,
          `lastname` text DEFAULT NULL,
          `username` text DEFAULT NULL,
          `role` text DEFAULT NULL,
          `nickname` text DEFAULT NULL,
          `emoji` text DEFAULT NULL,
          `lang` text DEFAULT NULL,
          `photo` text DEFAULT NULL,
          `banned` tinyint(4) DEFAULT NULL,
          `ban_comment` text DEFAULT NULL,
          `ban_date_from` int(11) DEFAULT NULL,
          `ban_date_to` int(11) DEFAULT NULL,
          `state_name` text DEFAULT NULL,
          `state_data` mediumtext DEFAULT NULL,
          `version` text DEFAULT NULL,
          `note` text DEFAULT NULL,
          PRIMARY KEY (`id`)
        );";

        $stats_new_users_sql = "CREATE TABLE IF NOT EXISTS `stats_new_users` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `date` int(11) DEFAULT NULL,
          `count` int(11) NOT NULL,
          PRIMARY KEY (`id`)
        );";

        $stats_messages_sql = "CREATE TABLE IF NOT EXISTS `stats_messages` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `date` int(11) DEFAULT NULL,
          `count` int(11) NOT NULL,
          PRIMARY KEY (`id`)
        );";

        $messages_sql = "CREATE TABLE IF NOT EXISTS `messages` (
          `id` bigint(20) NOT NULL AUTO_INCREMENT,
          `date` int(11) DEFAULT NULL,
          `user_id` bigint(20) DEFAULT NULL,
          `user` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
          `value` mediumtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
          PRIMARY KEY (`id`)
        );";

        $users = $this->db->query($user_sql)->execute();
        $stats_new_users = $this->db->query($stats_new_users_sql)->execute();
        $stats_messages = $this->db->query($stats_messages_sql)->execute();
        $messages = $this->db->query($messages_sql)->execute();
    }

    public function storeEvent()
    {
        $bot = $this->bot;
        $date = $bot->helper->midnight();

        // messages stats
        $isNewDate = $bot->db->table('stats_messages')->where('date', $date)->count() == 0;
        if ($isNewDate) {
            $bot->db->query("INSERT INTO stats_messages (date, count) VALUES ({$date}, 1)");
        } else {
            $bot->db->query("UPDATE stats_messages SET count = count + 1 WHERE date = {$date}");
        }

        // new users stats
        $isNewDate = $bot->db->table('stats_new_users')->where('date', $date)->count() == 0;
        if ($bot->user->isNewUser) {
            if ($isNewDate) {
                $bot->db->query("INSERT INTO stats_new_users (date, count) VALUES ({$date}, 1)");
            } else {
                $bot->db->query("UPDATE stats_new_users SET count = count + 1 WHERE date = {$date}");
            }
        } else {
            if ($isNewDate) {
                $bot->db->query("INSERT INTO stats_new_users (date, count) VALUES ({$date}, 0)");
            }
        }

        $update = $bot->update()->toArray();

        if ($bot->isSticker) {
            $update['message']['text'] = '🖼 Стикер';
        }
        if ($bot->isPhoto) {
            $update['message']['text'] = '🖼 Фотография';
        }
        if ($bot->isVideo) {
            $update['message']['text'] = '🎬 Видео';
        }
        if ($bot->isVideoNote) {
            $update['message']['text'] = '🎬 Видеосообщение';
        }
        if ($bot->isDocument) {
            $update['message']['text'] = '📎 Файл';
        }
        if ($bot->isAnimation) {
            $update['message']['text'] = '🖼 Gif';
        }
        if ($bot->isAudio) {
            $update['message']['text'] = '🎶 Аудио';
        }
        if ($bot->isVoice) {
            $update['message']['text'] = '🎤 Голосовое сообщение';
        }
        if ($bot->isContact) {
            $update['message']['text'] = '💌 Контакт';
        }
        if ($bot->isLocation) {
            $update['message']['text'] = '📍 Геолокация';
        }
        if ($bot->isVenue) {
            $update['message']['text'] = '📍 Место встречи';
        }
        if ($bot->isPoll) {
            $update['message']['text'] = '📊 Голосование';
        }

        $insert = [
            'date' => time(),
            'user_id' => $bot->from->id,
            'user' => $bot->from->fullname,
            'value' => json_encode($update, JSON_UNESCAPED_UNICODE)
        ];

        $bot->db->table('messages')
                ->insert($insert);
    }
}
