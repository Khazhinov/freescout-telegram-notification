<?php

namespace Modules\TelegramNotifiction\Entities;

use App\Conversation;

class ConversationObserver
{
    public function created(Conversation $conversation): void
    {
        $message = sprintf('Поступило новое обращение #%d\n%s\n\n%s', $conversation->number, $conversation->subject, $conversation->body);
        TelegramSender::send($message);
    }
}