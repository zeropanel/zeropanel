<?php

namespace App\Utils;

use Exception;
use TelegramBot\Api\BotApi;
use Telegram\Bot\Api;
use App\Models\Setting;

class Telegram
{
    /**
     * 发送讯息，默认给群组发送
     *
     * @param string $messageText
     * @param int    $chat_id
     */
    public static function Send($messageText, $chat_id = 0): void
    {
        if ($chat_id === 0) {
            $chat_id = Setting::obtain('telegram_general_group_id');
        }
        if (Setting::obtain('enable_telegram_bot') == true) {
            if (Setting::obtain('enable_new_telegram_bot') == true) {
                // 发送给非群组时使用异步
                $async = ($chat_id != Setting::obtain('telegram_general_group_id'));
                $bot = new Api(Setting::obtain('telegram_bot_token'), $async);
                $sendMessage = [
                    'chat_id'                   => $chat_id,
                    'text'                      => $messageText,
                    'parse_mode'                => '',
                    'disable_web_page_preview'  => false,
                    'reply_to_message_id'       => null,
                    'reply_markup'              => null
                ];
                $bot->sendMessage($sendMessage);
            } else {
                $bot = new BotApi(Setting::obtain('telegram_bot_token'));
                $bot->sendMessage($chat_id, $messageText);
            }
        }
    }

    /**
     * 以 Markdown 格式发送讯息，默认给群组发送
     *
     * @param string $messageText
     * @param int    $chat_id
     */
    public static function SendMarkdown(string $messageText, int $chat_id = 0): void
    {
        if ($chat_id === 0) {
            $chat_id = Setting::obtain('telegram_general_group_id');
        }
        if (Setting::obtain('enable_telegram_bot') == true) {
            if (Setting::obtain('enable_new_telegram_bot') == true) {
                // 发送给非群组时使用异步
                $async = ($chat_id != Setting::obtain('telegram_general_group_id'));
                $bot = new Api(Setting::obtain('telegram_bot_token'), $async);
                $sendMessage = [
                    'chat_id'                   => $chat_id,
                    'text'                      => $messageText,
                    'parse_mode'                => 'Markdown',
                    'disable_web_page_preview'  => false,
                    'reply_to_message_id'       => null,
                    'reply_markup'              => null
                ];
                try {
                    $bot->sendMessage($sendMessage);
                } catch (Exception $e) {
                    echo $e->getMessage();
                }
            } else {
                $bot = new BotApi(Setting::obtain('telegram_bot_token'));
                try {
                    $bot->sendMessage($chat_id, $messageText, 'Markdown');
                } catch (Exception $e) {
                    echo $e->getMessage();
                }
            }
        }
    }
    
    /**
     * 发送讯息，默认给管理员
     *
     * @param string $messageText
     * @param int    $chat_id
     */
    public static function PushToAdmin($messageText, $chat_id = 0): void
    {
        if (Setting::obtain('enable_telegram_bot') == true) {
            if (Setting::obtain('enable_new_telegram_bot') == true) {
                // 发送给非群组时使用异步
                $async = (!in_array($chat_id, (array)json_decode(Setting::obtain('telegram_general_admin_id'))));
                $bot = new Api(Setting::obtain('telegram_bot_token'), $async);
                $sendMessage = [
                    'chat_id'                   => $chat_id,
                    'text'                      => $messageText,
                    'parse_mode'                => '',
                    'disable_web_page_preview'  => false,
                    'reply_to_message_id'       => null,
                    'reply_markup'              => null
                ];
                try {
                    $bot->sendMessage($sendMessage);
                } catch (Exception $e) {
                    echo $e->getMessage();
                }
            } else {
                $bot = new BotApi(Setting::obtain('telegram_bot_token'));
                try {
                    $bot->sendMessage($chat_id, $messageText);
                } catch (Exception $e) {
                    echo $e->getMessage();
                }
            }
        }
    }

    /**
     * push message to channel
     *
     * @param string $messageText
     * @param int    $chat_id
     */
    public static function PushToChanel($messageText, $chat_id = 0): void
    {
        if ($chat_id === 0) {
            $chat_id = Setting::obtain('telegram_general_channel_id');
        }
        if (Setting::obtain('enable_telegram_bot') == true) {
            if (Setting::obtain('enable_new_telegram_bot') == true) {
                // 发送给非群组时使用异步
                $async = ($chat_id != Setting::obtain('telegram_general_channel_id'));
                $bot = new Api(Setting::obtain('telegram_bot_token'), $async);
                $sendMessage = [
                    'chat_id'                   => $chat_id,
                    'text'                      => $messageText,
                    'parse_mode'                => 'Markdown',
                    'disable_web_page_preview'  => false,
                    'reply_to_message_id'       => null,
                    'reply_markup'              => null
                ];
                try {
                    $bot->sendMessage($sendMessage);
                } catch (Exception $e) {
                    echo $e->getMessage();
                }
            } else {
                $bot = new BotApi(Setting::obtain('telegram_bot_token'));
                try {
                    $bot->sendMessage($chat_id, $messageText);
                } catch (Exception $e) {
                    echo $e->getMessage();
                }
            }
        }
    }
}
