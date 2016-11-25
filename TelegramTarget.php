<?php

namespace nastradamus39\tewloger;

use yii\helpers\VarDumper;
use yii\base\InvalidConfigException;
use yii\log\Logger;
use yii\log\Target;

class TelegramTarget extends Target
{

    public $botToken;

    public $chatId;

    public $webSend;

    public $webKey;

    public $webToken;

    public $webUrlApi;

    public $webUrlDetail;

    public function init()
    {
        parent::init();
        foreach (['botToken', 'chatId', 'webSend', 'webKey', 'webToken', 'webUrlApi', 'webUrlDetail'] as $property) {
            if ($this->$property === null) {
                throw new InvalidConfigException(self::className() . "::\$$property property must be set");
            }
        }
    }

    public function export()
    {
        $tBot = new TelegramClient($this->botToken, $this->chatId);

        $wBot = new WebClient([
            "key"   =>$this->webKey,
            "token" =>$this->webToken,
            "url"   =>$this->webUrlApi
        ]);

        foreach ($this->messages as $message) {

            list($text, $level, $category, $timestamp) = $message;
            $message = $this->formatMessage($message);

            if($this->webSend){

                $res = $wBot->send(HttpClient::METHOD_POST, 'logs', [
                    "type" => intval($level),
                    "message" => strval($text),
                    "file" => "text",
                    "line" => 12,
                    "key" => $this->webKey,
                    "order" => 0,
                    'time' => $timestamp
                ]);

                if($res && "success" === $res->status){
                    $message = $message."... <a href='".sprintf($this->webUrlDetail,$res->result->id)."'>more</a>";
                    $tBot->sendMessage($message);
                }else{
                    $tBot->sendMessage($text);
                }

            }else{
                $tBot->sendMessage($text);
            }
        }
    }

    public function formatMessage($message)
    {
        list($text, $level, $category, $timestamp) = $message;
        $level = Logger::getLevelName($level);
        if (!is_string($text)) {
            if ($text instanceof \Exception) {
                $text = (string) $text;
            } else {
                $text = VarDumper::export($text);
            }
        }

        $text = substr($text,0,150)."... ";

        $prefix = $this->getMessagePrefix($message);
        return date('Y-m-d H:i:s', $timestamp) . " {$prefix}[$level][$category] $text";
    }

}