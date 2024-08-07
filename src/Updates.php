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
        }
    }

    public function getUpdateType(): false|string
    {
        if (isset($this->inline_query)) {
            return 'inline_query';
        }
        if (isset($this->callback_query)) {
            return 'callback_query';
        }
        if (isset($this->edited_message)) {
            return 'edited_message';
        }
        if (isset($this->message->text)) {
            return 'message';
        }
        if (isset($this->message->photo)) {
            return 'photo';
        }
        if (isset($this->message->video)) {
            return 'video';
        }
        if (isset($this->message->audio)) {
            return 'audio';
        }
        if (isset($this->message->voice)) {
            return 'voice';
        }
        if (isset($this->message->contact)) {
            return 'contact';
        }
        if (isset($this->message->location)) {
            return 'location';
        }
        if (isset($this->message->reply_to_message)) {
            return 'reply_to_message';
        }
        if (isset($this->message->animation)) {
            return 'animation';
        }
        if (isset($this->message->sticker)) {
            return 'sticker';
        }
        if (isset($this->message->document)) {
            return 'document';
        }
        if (isset($this->message->new_chat_members)) {
            return 'new_chat_members';
        }
        if (isset($this->message->left_chat_member)) {
            return 'left_chat_member';
        }
        if (isset($this->my_chat_member)) {
            return 'my_chat_member';
        }
        if (isset($this->channel_post)) {
            return 'channel_post';
        }

        return false;
    }
}