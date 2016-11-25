<?php

namespace nastradamus39\telegram;

class TelegramBot
{
    const API_BASE_URL = 'https://api.telegram.org/bot';

    public $token;

    public $chatId;

    public function __construct($token,$chatId)
    {
        if(empty($token))
            throw new Exception("Token not set");

        if(empty($chatId))
            throw new Exception("Chatid not set");

        $this->token = $token;
        $this->chatId = $chatId;
    }

    public function sendMessage($text, $parse_mode = 'HTML', $disable_web_page_preview = false, $disable_notification = false)
    {
        $params = compact('text', 'parse_mode', 'disable_web_page_preview', 'disable_notification');
        $params['chat_id'] = $this->chatId;

        $opts = ['http' => [
            'method'  => "POST",
            'header'  => "Content-Type: application/json\r\n",
            'content' => json_encode($params)
        ]];

        $context  = stream_context_create($opts);
        $url = self::API_BASE_URL.$this->token."/sendMessage";
        $resp = file_get_contents($url, false, $context);
        return json_decode($resp);
    }
}