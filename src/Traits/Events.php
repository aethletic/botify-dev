<?php

namespace Botify\Traits;

trait Events
{
    public function onCommand($func)
    {
        if ($this->isCommand) {
            return call_user_func_array($func, [$this->message->text]);
        }
    }

    public function onSticker($func)
    {
        if ($this->isSticker) {
            return call_user_func_array($func, [$this->isSticker]);
        }
    }

    public function onVoice($func)
    {
        if ($this->isVoice) {
            return call_user_func_array($func, [$this->isVoice]);
        }
    }

    public function onDocument($func)
    {
        if ($this->isDocument) {
            return call_user_func_array($func, [$this->isDocument]);
        }
    }

    public function onAnimation($func)
    {
        if ($this->isAnimation) {
            return call_user_func_array($func, [$this->isAnimation]);
        }
    }

    public function onPhoto($func)
    {
        if ($this->isPhoto) {
            return call_user_func_array($func, [$this->isPhoto]);
        }
    }

    public function onAudio($func)
    {
        if ($this->isAudio) {
            return call_user_func_array($func, [$this->isAudio]);
        }
    }

    public function onVideoNote($func)
    {
        if ($this->isVideoNote) {
            return call_user_func_array($func, [$this->isVideoNote]);
        }
    }

    public function onContact($func)
    {
        if ($this->isContact) {
            return call_user_func_array($func, [$this->isContact]);
        }
    }

    public function onLocation($func)
    {
        if ($this->isLocation) {
            return call_user_func_array($func, [$this->isLocation]);
        }
    }

    public function onPoll($func)
    {
        if ($this->isPoll) {
            return call_user_func_array($func, [$this->isPoll]);
        }
    }

    public function onDice($func)
    {
        if ($this->isDice) {
            return call_user_func_array($func, [$this->isDice['emoji'], $this->isDice['value']]);
        }
    }

    public function onInline($func)
    {
        if ($this->isInline) {
            return call_user_func($func);
        }
    }

    public function onCallback($func)
    {
        if ($this->isCallback) {
            return call_user_func($func);
        }
    }

    public function onMessage($func)
    {
        if ($this->isMessage) {
            return call_user_func($func);
        }
    }

    public function onEditedMessage($func)
    {
        if ($this->isEditedMessage) {
            return call_user_func($func);
        }
    }

    public function onVideo($func)
    {
        if ($this->isVideo) {
            return call_user_func_array($func, [$this->isVideo]);
        }
    }

    public function fromPrivate($func)
    {
        if ($this->isPrivate) {
            return call_user_func($func);
        }
    }

    public function fromChannel($func)
    {
        if ($this->isChannel) {
            return call_user_func($func);
        }
    }

    public function fromGroup($func)
    {
        if ($this->isChannel) {
            call_user_func($func);
        }
    }

    public function fromSuperGroup($func)
    {
        if ($this->isChannel) {
            return call_user_func($func);
        }
    }

    public function onAdmin($func)
    {
        if ($this->isAdmin) {
            return call_user_func($func);
        }
    }

    public function onNewUser($func)
    {
        if ($this->user && $this->user->isNewUser) {
            return call_user_func($func);
        }
    }

    public function onNewVersion($func)
    {
        if ($this->user && $this->user->isNewVersion) {
            return call_user_func_array($func, [$this->config('bot.version')->first()]);
        }
    }

    public function onSpam($func)
    {
        if ($this->user && $this->user->isSpam) {
            return call_user_func_array($func, [$this->user->isSpam]);
        }
    }

    public function onBanned($func)
    {
        if ($this->user && $this->user->isBanned) {
            return call_user_func_array($func, [
                $this->data->get('ban_comment')->first(),
                $this->data->get('ban_date_from')->first(),
                $this->data->get('ban_date_to')->first(),
            ]);
        }
    }
}
