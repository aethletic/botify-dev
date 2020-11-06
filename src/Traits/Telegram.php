<?php

namespace Botify\Traits;

trait Telegram
{
    public function setWebhook($url, $extra = ['max_connections' => 70])
    {
        return $this->request(__FUNCTION__, array_merge(['url' => $url], $extra));
    }

    public function deleteWebhook()
    {
        return $this->request(__FUNCTION__);
    }

    public function getWebhookInfo()
    {
        return $this->request(__FUNCTION__);
    }

    public function getUpdates($offset = 0, $limit = 100, $extra = [])
    {
        return $this->request(__FUNCTION__, $this->buildRequestParams([
            'offset' => $offset,
            'limit' => $limit,
        ], false, $extra));
    }

    public function say($text, $keyboard = false, $extra = [])
    {
        return $this->sendMessage(
            $this->update(['*.chat.id', '*.from.id']),
            $text,
            $keyboard,
            $extra
        );
    }

    public function reply($text, $keyboard = false, $extra = [])
    {
        return $this->sendMessage(
            $this->update(['*.chat.id', '*.from.id']),
            $text,
            $keyboard,
            array_merge($extra, ['reply_to_message_id' => $this->update('*.message_id')])
        );
    }

    public function print($text)
    {
        return $this->say(print_r($text, true));
    }

    public function notify($text, $showAlert = false, $extra = [])
    {
        return $this->request('answerCallbackQuery', $this->buildRequestParams([
            'callback_query_id' => $this->update('callback_query.id'),
            'text' => $text,
            'show_alert' => $showAlert,
        ], false, $extra));
    }

    public function action($action = 'typing', $extra = [])
    {
        return $this->request('sendAction', $this->buildRequestParams([
            'chat_id' => $this->update(['*.chat.id', '*.from.id']),
            'action' => $action,
        ], false, $extra));
    }

    public function dice($chatId, $emoji = '🎲', $keyboard = false, $extra = [])
    {
        return $this->sendDice($this->update(['*.chat.id', '*.from.id']), $emoji, $keyboard, $extra);
    }

    public function isActive($chatId, $action = 'typing', $extra = [])
    {
        return $this->request('sendAction', $this->buildRequestParams([
            'chat_id' => $chatId,
            'action' => $action,
        ], false, $extra))->get('ok');
    }

    public function sendAction($chatId, $action = 'typing', $extra = [])
    {
        return $this->request(__FUNCTION__, $this->buildRequestParams([
            'chat_id' => $chatId,
            'action' => $action,
        ], false, $extra));
    }

    public function sendMessage($chatId, $text, $keyboard = false, $extra = [])
    {
        return $this->request(__FUNCTION__, $this->buildRequestParams([
            'chat_id' => $chatId,
            'text' => $text,
        ], $keyboard, $extra));
    }

    public function forwardMessage($chatId, $fromChatId, $messageId, $extra = [])
    {
        return $this->request('sendMessage', $this->buildRequestParams([
            'chat_id' => $chatId,
            'from_chat_id' => $fromChatId,
            'message_id' => $messageId,
        ], false, $extra));
    }

    public function sendReply($chatId, $messageId, $text = '', $keyboard = false, $extra = [])
    {
        return $this->request('sendMessage', $this->buildRequestParams([
            'chat_id' => $chatId,
            'text' => $text,
            'reply_to_message_id' => $messageId,
        ], $keyboard, $extra));
    }

    public function getMe()
    {
        return $this->request(__FUNCTION__);
    }

    public function sendPhoto($chatId, $photo, $caption = '', $keyboard = false, $extra = [])
    {
        return $this->request(__FUNCTION__, $this->buildRequestParams([
            'chat_id' => $chatId,
            'caption' => $caption,
            'photo' => $photo,
        ], $keyboard, $extra), true);
    }

    public function sendAudio($chatId, $audio, $caption = '', $keyboard = false, $extra = [])
    {
        return $this->request(__FUNCTION__, $this->buildRequestParams([
            'chat_id' => $chatId,
            'caption' => $caption,
            'audio' => $audio,
        ], $keyboard, $extra), true);
    }

    public function sendDocument($chatId, $document, $caption = '', $keyboard = false, $extra = [])
    {
        return $this->request(__FUNCTION__, $this->buildRequestParams([
            'chat_id' => $chatId,
            'caption' => $caption,
            'document' => $document,
        ], $keyboard, $extra), true);
    }

    public function sendAnimation($chatId, $animation, $caption = '', $keyboard = false, $extra = [])
    {
        return $this->request(__FUNCTION__, $this->buildRequestParams([
            'chat_id' => $chatId,
            'caption' => $caption,
            'animation' => $animation,
        ], $keyboard, $extra), true);
    }

    public function sendVideoNote($chatId, $videoNote, $keyboard = false, $extra = [])
    {
        return $this->request(__FUNCTION__, $this->buildRequestParams([
            'chat_id' => $chatId,
            'video_note' => $videoNote,
        ], $keyboard, $extra), true);
    }

    public function sendSticker($chatId, $sticker, $keyboard = false, $extra = [])
    {
        return $this->request(__FUNCTION__, $this->buildRequestParams([
            'chat_id' => $chatId,
            'sticker' => $sticker,
        ], $keyboard, $extra), true);
    }

    public function sendVoice($chatId, $voice, $caption = '', $keyboard = false, $extra = [])
    {
        return $this->request(__FUNCTION__, $this->buildRequestParams([
            'chat_id' => $chatId,
            'caption' => $caption,
            'voice' => $voice,
        ], $keyboard, $extra), true);
    }

    public function sendMediaGroup($chatId, $media, $extra = [])
    {
        return $this->request(__FUNCTION__, $this->buildRequestParams([
            'chat_id' => $chatId,
            'media' => $caption,
        ], false, $extra), true);
    }

    public function sendLocation($chatId, $latitude, $longitude, $keyboard = false, $extra = [])
    {
        return $this->request(__FUNCTION__, $this->buildRequestParams([
            'chat_id' => $chatId,
            'latitude' => $latitude,
            'longitude' => $longitude,
        ], $keyboard, $extra));
    }

    public function sendDice($chatId, $emoji = '🎲', $keyboard = false, $extra = [])
    {
        $emoji = str_ireplace(['dice', 'кубик'], '🎲', $emoji);
        $emoji = str_ireplace(['darts', 'dart', 'дротик', 'дартс'], '🎯', $emoji);
        $emoji = str_ireplace(['basketball', 'баскетбол'], '🏀');
        $emoji = str_ireplace(['football', 'футбол'], '⚽️', $emoji);

        return $this->request(__FUNCTION__, $this->buildRequestParams([
            'chat_id' => $chatId,
            'emoji' => $emoji,
        ], $keyboard, $extra));
    }

    public function getUserProfilePhotos($userId, $offset = 0, $limit = 100)
    {
        return $this->request(__FUNCTION__, [
            'user_id' => $userId,
            'offset' => $offset,
            'limit' => $limit,
        ]);
    }

    public function getFile($fileId)
    {
        return $this->request(__FUNCTION__, [
            'file_id' => $fileId,
        ]);
    }

    public function saveFile($fileUrl, $savePath)
    {
        $extension = stripos(basename($fileUrl), '.') !== false ? end(explode('.', basename($fileUrl))) : '';
        $savePath = str_ireplace(['{ext}', '{extension}', '{file_ext}'], $extension, $savePath);
        $savePath = str_ireplace(['{base}', '{basename}', '{base_name}', '{name}'], basename($fileUrl), $savePath);
        $savePath = str_ireplace(['{time}'], time(), $savePath);
        $savePath = str_ireplace(['{md5}'], md5(time().mt_rand()), $savePath);
        $savePath = str_ireplace(['{rand}','{random}','{rand_name}','{random_name}'], md5(time().mt_rand()) . ".$extension", $savePath);
        $savePath = str_ireplace(['{base}', '{basename}', '{base_name}', '{name}'], basename($fileUrl), $savePath);

        file_put_contents($savePath, file_get_contents($this->buildRequestFileUrl($fileUrl)));

        return basename($savePath);
    }

    public function kickChatMember($chatId, $userId, $untilDate)
    {
        return $this->request(__FUNCTION__, [
            'chat_id' => $chatId,
            'user_id' => $userId,
            'until_date' => $untilDate,
        ]);
    }

    public function unbanChatMember($chatId, $userId)
    {
        return $this->request(__FUNCTION__, [
            'chat_id' => $chatId,
            'user_id' => $userId,
        ]);
    }

    public function restrictChatMember($chatId, $userId, $permissions, $untilDate = false)
    {
        return $this->request(__FUNCTION__, [
            'chat_id' => $chatId,
            'user_id' => $userId,
            'permissions' => $permissions,
            'until_date' => $untilDate,
        ]);
    }

    public function setMyCommands($commands)
    {
        return $this->request(__FUNCTION__, [
            'commands' => $commands,
        ]);
    }

    public function getMyCommands()
    {
        return $this->request(__FUNCTION__);
    }

    public function editMessageText($messageId, $chatId, $text = '', $keyboard = false, $extra = [])
    {
        return $this->request(__FUNCTION__, $this->buildRequestParams([
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'text' => $text,
        ], $keyboard, $extra));
    }

    public function editMessageCaption($messageId, $chatId, $caption = '', $keyboard = false, $extra = [])
    {
        return $this->request(__FUNCTION__, $this->buildRequestParams([
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'caption' => $caption,
        ], $keyboard, $extra));
    }

    public function editMessageMedia($messageId, $chatId, $media, $keyboard = false, $extra = [])
    {
        return $this->request(__FUNCTION__, $this->buildRequestParams([
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'media' => $media,
        ], $keyboard, $extra));
    }

    public function editMessageReplyMarkup($messageId, $chatId, $keyboard = false, $extra = [])
    {
        return $this->request(__FUNCTION__, $this->buildRequestParams([
            'chat_id' => $chatId,
            'message_id' => $messageId,
        ], $keyboard, $extra));
    }

    public function deleteMessage($messageId, $chatId)
    {
        return $this->request(__FUNCTION__, [
            'chat_id' => $chatId,
            'message_id' => $messageId,
        ]);
    }

    public function getStickerSet($name)
    {
        return $this->request(__FUNCTION__, [
            'name' => $name,
        ]);
    }

    public function uploadStickerFile($userId, $pngSticker)
    {
        return $this->request(__FUNCTION__, [
            'user_id' => $userId,
            'png_sticker' => $pngSticker,
        ], true);
    }

    public function sendGame($chatId, $gameShortName, $keyboard = false, $extra = [])
    {
        return $this->request(__FUNCTION__, $this->buildRequestParams([
            'chat_id' => $chatId,
            'game_short_name' => $gameShortName,
        ], $keyboard, $extra));
    }

    public function answerCallbackQuery($extra = [])
    {
        return $this->request(__FUNCTION__, $extra);
    }

    public function answerInlineQuery($results = [], $extra = [])
    {
        return $this->request(__FUNCTION__, $this->buildRequestParams([
            'inline_query_id' => $this->inlineId,
            'results' => json_encode($results),
        ], false, $extra));
    }
}
