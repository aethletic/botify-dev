<?php

namespace Botify\Modules;

use Botify\Util\Collection;

// Можно юзать отдельно для других юзеров
class User extends Module
{
    private $data = [];
    private $userId;

    public $isNewUser = false;
    public $isNewVersion = false;
    public $isSpam = false;
    public $isBanned = false;

    public function __construct($userId = false, $inserteUserIfNotExists = false)
    {
        parent::__construct();

        if (!$userId) {
            $this->data = new Collection([]);
        }

        $this->userId = $userId;

        // Если юзер существует в БД, получаем его данные
        if ($this->exists($userId)) {
            $this->data = new Collection($this->getDataById($userId));
            $this->diffBotVersion();

            // check spam time and update last_message
            $diffMessageTime = time() - $this->data->get('last_message')->first();

            $timeout = $this->bot->config('general.spam_timeout')->first();

            if ($diffMessageTime <= $timeout) {
              $this->isSpam = $timeout - $diffMessageTime;
            } else {
              $this->update(['last_message' => time()]);
            }

            $this->isBanned = $this->data->get('banned')->first() == 1;

            return;
        }

        $this->isNewUser = true;

        if (!$inserteUserIfNotExists) {
            $this->data = new Collection([]);
            return;
        }

        // Создаем новую запись о юзере
        $data = [
            // Общная информация
            'user_id' => $this->bot->from->id, // telegram id юзера
            'active' => 1, // юзер не заблокировал бота
            'fullname' => $this->bot->from->fullname, // имя фамилия
            'firstname' => $this->bot->from->firstname, // имя
            'lastname' => $this->bot->from->lastname, // фамилия
            'username' => $this->bot->from->username, // telegram юзернейм
            'lang' => $this->bot->from->username, // язык
            'photo' => null, // фото

            // Сообщения
            'first_message' => time(), // первое сообщение (дата регистрации) (unix)
            'last_message' => time(), // последнее сообщение (unix)
            'source' => null, // откуда пользователь пришел (/start botcatalog)

            // Бан
            'banned' => 0, // забанен или нет
            'ban_comment' => null, // комментарий при бане
            'ban_date_from' => null, // бан действует с (unix)
            'ban_date_to' => null, // бан до (unix)

            // Стейты
            'state_name' => null, // название стейта
            'state_data' => null, // значение стейта

            // Дополнительно
            'role' => 'user', // группа юзера
            'nickname' => null, // никнейм (например для игровых ботов)
            'emoji' => null, // эмодзи/иконка (префикс)

            // Служебное
            'note' => null, // заметка о юзере
            'version' => $this->bot->config('bot.version')->first(), // последняя версия бота с которой взаимодействовал юзер
        ];

        $data = array_merge($data, $this->bot->config('database.fields')->toArray());

        $this->db
             ->table('users')
             ->insert($data);

        $this->data = new Collection($data);
    }

    public function get($key, $default = false)
    {
        return $this->data->get($key, $default)->first();
    }

    public function update($data)
    {
        return $this->updateById($this->userId, $data);
    }

    public function updateById($userId, $data)
    {
        return $this->db
                    ->table('users')
                    ->where('user_id', $userId)
                    ->update($data);
    }

    public function exists($userId)
    {
        return $this->db
                    ->table('users')
                    ->where('user_id', $userId)
                    ->count() > 0;
    }

    // Получить данные о юзере
    public function getDataById($userId)
    {
        if (!$this->exists($userId)) {
            return false;
        }

        return $this->db
                    ->table('users')
                    ->where('user_id', $userId)
                    ->first();
    }

    private function diffBotVersion()
    {
        $userVersion = $this->data->get('version')->first();
        $currentVersion = $this->bot->config('bot.version')->first();

        $this->isNewVersion = $userVersion != $currentVersion;

        if ($this->isNewVersion) {
            $this->update(['version' => $currentVersion]);
        }
    }
}
