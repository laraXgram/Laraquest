<?php

namespace LaraGram\Laraquest;

use LaraGram\Laraquest\Updates\BusinessConnection;
use LaraGram\Laraquest\Updates\BusinessMessagesDeleted;
use LaraGram\Laraquest\Updates\CallbackQuery;
use LaraGram\Laraquest\Updates\ChatBoostRemoved;
use LaraGram\Laraquest\Updates\ChatBoostUpdated;
use LaraGram\Laraquest\Updates\ChatJoinRequest;
use LaraGram\Laraquest\Updates\ChatMemberUpdated;
use LaraGram\Laraquest\Updates\ChosenInlineResult;
use LaraGram\Laraquest\Updates\InlineQuery;
use LaraGram\Laraquest\Updates\Message;
use LaraGram\Laraquest\Updates\MessageReactionCountUpdated;
use LaraGram\Laraquest\Updates\MessageReactionUpdated;
use LaraGram\Laraquest\Updates\Poll;
use LaraGram\Laraquest\Updates\PollAnswer;
use LaraGram\Laraquest\Updates\PreCheckoutQuery;
use LaraGram\Laraquest\Updates\ShippingQuery;
use LaraGram\Exceptions\InvalidUpdateType;

/**
 * @property int $update_id
 * @property Message $message
 * @property Message $edited_message
 * @property Message $channel_post
 * @property Message $edited_channel_post
 * @property BusinessConnection $business_connection
 * @property Message $business_message
 * @property Message $edited_business_message
 * @property BusinessMessagesDeleted $deleted_business_messages
 * @property MessageReactionUpdated $message_reaction
 * @property MessageReactionCountUpdated $message_reaction_count
 * @property InlineQuery $inline_query
 * @property ChosenInlineResult $chosen_inline_result
 * @property CallbackQuery $callback_query
 * @property ShippingQuery $shipping_query
 * @property PreCheckoutQuery $pre_checkout_query
 * @property Poll $poll
 * @property PollAnswer $poll_answer
 * @property ChatMemberUpdated $my_chat_member
 * @property ChatMemberUpdated $chat_member
 * @property ChatJoinRequest $chat_join_request
 * @property ChatBoostUpdated $chat_boost
 * @property ChatBoostRemoved $removed_chat_boost
 */
trait Updates
{
    private string $update_type;

    public function __construct()
    {
        if (class_exists("LaraGram\\Config\\Repository")) {
            $this->update_type = config()->get('bot.UPDATE_TYPE');
        } else {
            $this->update_type = $_ENV['UPDATE_TYPE'];
        }
    }
    public function __get($name)
    {
        if (isset($this->update_type) && $this->update_type == 'global') {
            global $data;
            $update = json_decode($data['argv'][1]);
        } elseif ($this->update_type == 'sync' || !isset($this->update_type)) {
            $update = json_decode(file_get_contents('php://input'));
        } elseif ($this->update_type == 'openswoole' || $this->update_type == 'swoole') {
            global $swoole;
            $update = $swoole;
        } elseif ($this->update_type == 'polling') {
            global $data;
            $update = $data;
        }

        return ($update->{$name}) ?? null;
    }

    public static function polling(callable $callback): void
    {
        $lastUpdateId = null;
        $polling = new Laraquest();
        while (true){
            $updates = $polling->getUpdates($lastUpdateId + 1, timeout: 100)['result'];
            foreach ($updates as $update){
                $lastUpdateId = $update['update_id'];
                global $data;
                $data = json_decode(json_encode($update));
                $callback($polling);
            }
            sleep(0.5);
        }
    }

    public function getData()
    {
        if (class_exists("LaraGram\\Config\\Repository")) {
            $update_type = config()->get('bot.UPDATE_TYPE');
        } else {
            $update_type = $_ENV['UPDATE_TYPE'];
        }

        if (isset($update_type) && $update_type == 'global') {
            global $data;
            return json_decode($data['argv'][1]);
        } elseif ($update_type == 'sync' || !isset($update_type)) {
            return json_decode(file_get_contents('php://input'));
        } elseif ($update_type == 'openswoole' || $update_type == 'swoole') {
            global $swoole;
            return $swoole;
        } elseif ($this->update_type == 'polling') {
            global $data;
            return $data;
        }

        return false;
    }

    /**
     * This function returns the type of the update.
     * @return false|string
     * @throws InvalidUpdateType
     */
    public function getUpdateType(): false|string
    {
        $update = $this->getData();
        return match (true) {
            isset($update->inline_query) => 'inline_query',
            isset($update->callback_query) => 'callback_query',
            isset($update->edited_message) => 'edited_message',
            isset($update->message) => $this->getUpdateMessageSubType($update->message),
            isset($update->my_chat_member) => 'my_chat_member',
            isset($update->channel_post) => 'channel_post',
            default => false
        };
    }

    /**
     * This function returns the type of the message.
     * @param object $message
     * @return string
     * @throws InvalidUpdateType
     */
    private function getUpdateMessageSubType(object $message): string
    {
        return match (true) {
            isset($message->text) => 'message',
            isset($message->photo) => 'photo',
            isset($message->video) => 'video',
            isset($message->audio) => 'audio',
            isset($message->voice) => 'voice',
            isset($message->contact) => 'contact',
            isset($message->location) => 'location',
            isset($message->reply_to_message) => 'reply_to_message',
            isset($message->animation) => 'animation',
            isset($message->sticker) => 'sticker',
            isset($message->document) => 'document',
            isset($message->new_chat_members) => 'new_chat_members',
            isset($message->left_chat_member) => 'left_chat_member',
            default => throw new InvalidUpdateType('Unknown message type')
        };
    }
}