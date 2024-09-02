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
    public function __get($name)
    {
        if (isset($_ENV['UPDATE_TYPE']) && $_ENV['UPDATE_TYPE'] == 'global') {
            global $data;
            $update = json_decode($data['argv'][1]);
        } elseif ((isset($_ENV['UPDATE_TYPE']) && $_ENV['UPDATE_TYPE'] == 'sync') || !isset($_ENV['UPDATE_TYPE'])) {
            $update = json_decode(file_get_contents('php://input'));
        } elseif (isset($_ENV['UPDATE_TYPE']) && $_ENV['UPDATE_TYPE'] == 'openswoole') {
            global $swoole;
            $update = $swoole;
        }

        return ($update->{$name}) ?? null;
    }

    public function getData()
    {
        if (isset($_ENV['UPDATE_TYPE']) && $_ENV['UPDATE_TYPE'] == 'global') {
            global $data;
            return json_decode($data['argv'][1]);
        } elseif ((isset($_ENV['UPDATE_TYPE']) && $_ENV['UPDATE_TYPE'] == 'sync') || !isset($_ENV['UPDATE_TYPE'])) {
            return json_decode(file_get_contents('php://input'));
        } elseif (isset($_ENV['UPDATE_TYPE']) && $_ENV['UPDATE_TYPE'] == 'openswoole') {
            global $swoole;
            return $swoole;
        }
    }

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