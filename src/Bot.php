<?php

namespace Botify;

use Botify\Traits\Router;
use Botify\Traits\Telegram;
use Botify\Traits\Request;
use Botify\Traits\Events;
use Botify\Util\Collection;
use Botify\Util\Container;
use Botify\Util\Helper;
use Botify\Modules\Log;
use Botify\Modules\Database;
use Botify\Modules\User;
use Botify\Modules\Cache;
use Botify\Modules\State;
use Botify\Modules\Localization;

define('__BOTIFY_VERSION__', '5.0.0');

class Bot extends Container
{
    use Router;
    use Telegram;
    use Request;
    use Events;

    public $updateId = -1;
    public $chat;
    public $from;
    public $file;
    public $message;
    public $inline;
    public $callback;

    public $isCallback = false;
    public $isInline = false;
    public $isMessage = false;
    public $isEditedMessage = false;
    public $isBot = false;
    public $isSticker = false;
    public $isVoice = false;
    public $isAnimation = false;
    public $isDocument = false;
    public $isAudio = false;
    public $isPhoto = false;
    public $isVideo = false;
    public $isVideoNote = false;
    public $isContact = false;
    public $isLocation = false;
    public $isVenue = false;
    public $isDice = false;
    public $isNewChatMembers = false;
    public $isLeftChatMember = false;
    public $isNewChatTitle = false;
    public $isNewChatPhoto = false;
    public $isDeleteChatPhoto = false;
    public $isChannelChatCreated = false;
    public $isMigrateToChatId = false;
    public $isMigrateFromChatId = false;
    public $isPinnedMessage = false;
    public $isInvoice = false;
    public $isSucessfulPayment = false;
    public $isConnectedWebsite = false;
    public $isPassportData = false;
    public $isReplyMarkup = false;
    public $isReply = false;
    public $isCaption = false;
    public $isCommand = false;
    public $isForward = false;
    public $isSuperGroup = false;
    public $isGroup = false;
    public $isChannel = false;
    public $isPrivate = false;
    public $isPoll = false;
    public $isAdmin = false;

    private $startTime;
    private $token;
    private $update = false;
    private $apiUrl = 'https://api.telegram.org/bot';
    private $apiFileUrl = 'https://api.telegram.org/file/bot';
    private $config = [
        'bot' => [
            'token' => '1234567890:ABC_TOKEN',
            'name' => 'Botify',
            'username' => 'botify_bot',
            'handler' => 'https://example.com/bot.php',
            'version' => '1.0',
        ],
        'general' => [
            'debug' => true,
            'timezone' => 'Europe/Samara',
            'spam_timeout' => 1,
            'default_lang' => 'en',
            'max_system_load' => 2,
            'max_execution_time' => 60,
        ],
        'admin' => [
            'list' => [
                'aethletic' => 'password',
                '436432850' => 'password',
            ]
        ],
        'telegram' => [
            'parse_mode' => 'html',
        ],
        'database' => [
            'enable' => false,
            'supported_drivers' => ['mysql', 'sqlite'],
            'driver' => 'mysql',
            'host' => 'localhost',
            'database' => 'botify',
            'username' => 'user',
            'password' => 'password',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'lazy' => true,
            'auto_create_tables' => true,
        ],
        'cache' => [
            'enable' => false,
            'supported_drivers' => ['memcached', 'redis'],
            'driver' => 'memcached',
            'host'  => 'localhost',
            'port' => '11211',
        ],
        'log' => [
            'enable' => false,
            'dir' => '/path/to/logs/dir',
            'store_events_in_db' => false,
        ],
        'extensions' => [
            'vendor_name1.extension_name1' => [
                'enable' => false,
                '...' => '...',
            ],
            'vendor_name2.extension_name2' => [
                'enable' => false,
                '...' => '...',
            ],
        ],
    ];

    public $keyboard = false;
    public $helper = false;
    public $db = false;
    public $cache = false;
    public $state = false;
    public $user = false;

    public function __construct($token, $config = [], $debugUpdate = false)
    {
        $this->startTime = microtime(true);

        parent::__construct();

        $this->token = $token;
        $this->config = new Collection(array_merge($this->config, $config));

        $this->keyboard = new Keyboard;
        $this->helper = new Helper;

        $this->initVars($debugUpdate);

        $this->includeModules();

        if ($this->config('general.store_events_in_db', false)->first() && $this->db && $this->user) {
            (new Database)->storeEvent();
        }
    }

    private function includeModules()
    {
        // Логирование
        if ($this->config('log.enable', false)->first()) {
            if ($logDir = $this->config('log.dir', false)->first()) {
                $this->log = new Log($logDir);
                if ($this->isUpdate()) {
                    $this->log->write($this->update()->toArray(), 'AUTO');
                }
            }
        }

        // База данных
        if ($this->config('database.enable', false)->first()) {
            $this->db = (new Database)();
        }

        // Cache
        if ($this->config('cache.enable', false)->first()) {
            $this->cache = (new Cache)($this->config('cache', ['driver' => false])->toArray());
        }

        // Стейты
        if ($this->db) {
            $this->state = new State;
        }

        // Пользователь
        if ($this->db && $this->update) {
            $this->user = new User($this->from->id, true);
        }

        // Пользователь
        if ($this->config('localization.dir', false)->first()) {
            $this->lang = new Localization($this->user
                ? $this->user('lang')
                : isset($this->from->lang)
                    ? $this->from->lang
                    : $this->config('localization.default_language', 'en')->first()
            );
        }
    }

    public function keyboard($keyboard = false, $oneTime = false, $resize = true)
    {
        if (!$keyboard) {
            return $this->keyboard->hide();
        }
        return $this->keyboard->show($keyboard, $oneTime, $resize);
    }

    public function isUpdate() : bool
    {
        return $this->update !== false;
    }

    public function getUpdate() : array
    {
        return $this->update->toArray();
    }

    public function setUpdate($update)
    {
        $this->startTime = microtime(true);
        $this->update = $update;
        $this->initVars($update);
    }

    public function update($keys = false, $default = false)
    {
        if ($keys) {
            $keys = is_array($keys) ? $keys : [$keys];
            foreach ($keys as $key) {
                $value = $this->update()->get($key, $default);
                if ($value) {
                    if ($result = $value->first()) {
                        return $result;
                    }
                }
            }
            return false;
        }
        return $this->update;
    }

    public function config($key, $default = false)
    {
        return $this->config->get($key, $default);
    }

    public function user($key, $default = false)
    {
        if (!$this->user) {
            return false;
        }

        return $this->user->get($key, $default);
    }

    public function lang($name, $replace = false)
    {
        return $this->lang->msg($name, $replace);
    }

    public function longpoll($func)
    {
        $pendingUpdateCount = $this->getWebhookInfo()->get('result')->pending_update_count;

        $botify = '| Botify v' . __BOTIFY_VERSION__ . " | Pending update count: {$pendingUpdateCount} |";
        $line = '+' . str_repeat('-', strlen($botify) - 2) . '+';

        echo $line . PHP_EOL;
        echo $botify . PHP_EOL;
        echo $line . PHP_EOL;

        echo "Long polling started ..." . PHP_EOL;

        while(true) {
            foreach ($this->getUpdates($this->updateId + 1, 1)->get('result') as $update) {
                $this->setUpdate($update);
                call_user_func($func);
            }
        }
    }

    public function sendJson()
    {
        if (!$this->isUpdate()) {
          return false;
        }

        return $this->request('sendMessage', [
            'chat_id' => $this->chat->id,
            'text' => '<code>'.json_encode($this->update->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).'</code>',
            'parse_mode' => 'html',
        ]);
    }

    public function time($lenght = 6)
    {
        return round(microtime(true) - $this->startTime, $lenght);
    }

    public function auth($input)
    {
        if (!$this->isAdmin) {
            return false;
        }

        return $input == $this->config("admin.list.{$this->from->username}", $this->config("admin.list.{$this->from->id}", false))->first();
    }

    private function initVars($debugUpdate = false) : void
    {
        $input = $debugUpdate ? $debugUpdate : file_get_contents('php://input');
        if ($input) {
            $input = is_object($input) ? json_decode(json_encode($input), true) : $input;
            $this->update = is_array($input) ? new Collection($input) : new Collection(json_decode($input, true));
        }

        if (!$this->update) {
            return;
        }

        $update = $this->update;

        $this->updateId = $update->getLast('update_id', false);

        $this->chat = new \stdClass;
        $this->chat->id = $update->getLast('*.chat.id', false);
        $this->chat->isBot = $update->getLast('*.chat.is_bot', false);
        $this->chat->firstname = trim($update->getLast('*.chat.first_name', false));
        $this->chat->lastname = trim($update->getLast('*.chat.last_name', false));
        $this->chat->username = $update->getLast('*.chat.username', false);
        $this->chat->fullname = trim($this->chat->firstname . ' ' . $this->chat->lastname);
        $this->chat->type = $update->getLast('*.chat.type', false);
        $this->chat->lang = $update->getLast('*.chat.language_code', 'en');

        $this->from = new \stdClass;
        $this->from->id = $update->getLast('*.from.id', false);
        $this->from->isBot = $update->getLast('*.from.is_bot', false);
        $this->from->firstname = trim($update->getLast('*.from.first_name', false));
        $this->from->lastname = trim($update->getLast('*.from.last_name', false));
        $this->from->fullname = trim($this->from->firstname . ' ' . $this->from->lastname);
        $this->from->username = $update->getLast('*.from.username', false);
        $this->from->lang = $update->getLast('*.from.language_code', $this->config('localization.default_language', 'en')->first());

        $this->message = new \stdClass;
        $this->message->id = $update->getLast('*.message_id', false);
        $this->message->text = $update->getLast('*.text', $update->getLast('*.caption',  $update->getLast('callback_query.data', false)));

        $this->inline = new \stdClass;
        $this->inline->id = $update->getLast('inline_query.id', false);
        $this->inline->query = $update->getLast('inline_query.query', false);
        $this->inline->offset = $update->getLast('inline_query.offset', false);

        $this->callback = new \stdClass;
        $this->callback->id = $update->getLast('callback_query.id', false);
        $this->callback->data = $update->getLast('callback_query.data', false);

        $this->file = new \stdClass;
        $this->file->id = $update->get('*.*.file_id')->first();
        $this->file->uniqueId = $update->get('*.*.file_unique_id')->first();
        $this->file->size = $update->get('*.*.file_size')->first();

        $this->isCallback = $update->get('callback_query', false)->first();
        $this->isInline = $update->get('inline_query', false)->first();
        $this->isMessage = $update->get('message', false)->first();
        $this->isEditedMessage = $update->get('edited_message', false)->first();

        // проверка админа
        $adminList = $this->config('admin.list')->toArray();
        if (array_key_exists($this->from->id, $adminList) || array_key_exists($this->from->username, $adminList)) {
            $this->isAdmin = true;
        }

        if (!$this->isMessage && !$this->isEditedMessage) {
            return;
        }

        $key = $this->isMessage ? 'message' : 'edited_message';

        $this->isBot                = data_get($update, '*.from.is_bot', false);
        $this->isSticker            = data_get($update, '*.sticker', false);
        $this->isVoice              = data_get($update, '*.voice', false);
        $this->isAnimation          = data_get($update, '*.animation', false);
        $this->isDocument           = data_get($update, '*.document', false);
        $this->isAudio              = data_get($update, '*.audio', false);
        $this->isPhoto              = data_get($update, '*.photo', false);
        $this->isVideo              = data_get($update, '*.video', false);
        $this->isPoll               = data_get($update, '*.poll', false);
        $this->isVideoNote          = data_get($update, '*.video_note', false);
        $this->isContact            = data_get($update, '*.contact', false);
        $this->isLocation           = data_get($update, '*.location', false);
        $this->isVenue              = data_get($update, '*.venue', false);
        $this->isDice               = data_get($update, '*.dice', false);
        $this->isNewChatMembers     = data_get($update, '*.new_chat_members', false);
        $this->isLeftChatMember     = data_get($update, '*.left_chat_member', false);
        $this->isNewChatTitle       = data_get($update, '*.new_chat_title', false);
        $this->isNewChatPhoto       = data_get($update, '*.new_chat_photo', false);
        $this->isDeleteChatPhoto    = data_get($update, '*.delete_chat_photo', false);
        $this->isChannelChatCreated = data_get($update, '*.channel_chat_created', false);
        $this->isMigrateToChatId    = data_get($update, '*.migrate_to_chat_id', false);
        $this->isMigrateFromChatId  = data_get($update, '*.migrate_from_chat_id', false);
        $this->isPinnedMessage      = data_get($update, '*.pinned_message', false);
        $this->isInvoice            = data_get($update, '*.invoice', false);
        $this->isSucessfulPayment   = data_get($update, '*.successful_payment', false);
        $this->isConnectedWebsite   = data_get($update, '*.connected_website', false);
        $this->isPassportData       = data_get($update, '*.passport_data', false);
        $this->isReplyMarkup        = data_get($update, '*.reply_markup', false);
        $this->isReply              = data_get($update, '*.reply_to_message', false);
        $this->isCaption            = data_get($update, '*.caption', false);

        if (array_key_exists('entities', $this->update[$key]))
            $this->isCommand = $this->update[$key]['entities'][0]['type'] == 'bot_command' ? true : false;

        if (array_key_exists('forward_date', $this->update[$key]) || array_key_exists('forward_from', $this->update[$key]))
            $this->isForward = true;

        if ($this->update[$key]['chat']['type'] == 'supergroup')
            $this->isSuperGroup = true;

        if ($this->update[$key]['chat']['type'] == 'group')
            $this->isGroup = true;

        if ($this->update[$key]['chat']['type'] == 'channel')
            $this->isChannel = true;

        if ($this->update[$key]['chat']['type'] == 'private')
            $this->isPrivate = true;
    }

}
