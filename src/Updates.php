<?php

namespace LaraGram\Laraquest;

use LaraGram\Laraquest\Exceptions\InvalidGetUpdateType;
use LaraGram\Laraquest\Exceptions\InvalidUpdateType;
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
use LaraGram\Laraquest\Updates\PaidMediaPurchased;
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
 * @property PaidMediaPurchased $purchased_paid_media
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
    private float $polling_sleep_time;
    private int $polling_timeout;
    private int $polling_limit;
    private array|null $polling_allowed_updates;

    public function __construct()
    {
        $getConfigValue = function ($key, $default, $file) {
            return class_exists("LaraGram\\Config\\Repository")
                ? config("$file.$key") ?? $default
                : ($_ENV[$key] ?? $default);
        };

        $this->update_type = $getConfigValue('update_type', 'sync', 'laraquest');
        $this->polling_sleep_time = $getConfigValue('sleep_interval', 0.5, 'laraquest.polling');
        $this->polling_timeout = $getConfigValue('timeout', 100, 'laraquest.polling');
        $this->polling_limit = $getConfigValue('limit', 100, 'laraquest.polling');
        $allowed_updates = $getConfigValue('allow_updates', ["*"], 'laraquest.polling');
        $this->polling_allowed_updates = $allowed_updates === ["*"] ? null : $allowed_updates;
    }
    public function __get($name)
    {
        global $argv;
        global $data;
        global $swoole;
        $update = match ($this->update_type){
            'sync' => json_decode(file_get_contents('php://input')),
            'global' => json_decode($argv[1] ?? ''),
            'openswoole', 'swoole' => $swoole,
            'polling' => $data,
            default => throw new InvalidGetUpdateType("Unknown get update type")
        };

        return ($update->{$name}) ?? null;
    }

    public static function polling(callable $callback): void
    {
        global $data;

        $lastUpdateId = null;
        $polling = new Laraquest();

        try {
            while (true){
                $updates = $polling->getUpdates(
                    $lastUpdateId + 1, $polling->polling_limit,
                    $polling->polling_timeout, $polling->polling_allowed_updates
                )['result'];

                foreach ($updates as $update){
                    $lastUpdateId = $update['update_id'];
                    $data = json_decode(json_encode($update));

                    $callback($polling);
                }

                sleep($polling->polling_sleep_time);
            }
        } catch (\Exception $exception){
            file_put_contents('laraquest.log', $exception->getMessage() . PHP_EOL, FILE_APPEND);
        }
    }

    public function getData()
    {
        global $argv;
        global $data;
        global $swoole;
        return match ($this->update_type){
            'sync' => json_decode(file_get_contents('php://input')),
            'global' => json_decode($argv[1] ?? ''),
            'openswoole', 'swoole' => $swoole,
            'polling' => $data,
            default => throw new InvalidGetUpdateType("Unknown get update type")
        };
    }

    /**
     * This function returns the type of the update.
     *
     * @return false|string
     * @throws InvalidUpdateType
     */
    public function getUpdateType(): false|string
    {
        return match (true) {
            isset($this->message) => $this->getUpdateMessageSubType($this->message),
            isset($this->edited_message) => $this->getUpdateMessageSubType($this->edited_message),
            isset($this->channel_post) => $this->getUpdateMessageSubType($this->channel_post),
            isset($this->edited_channel_post) => $this->getUpdateMessageSubType($this->edited_channel_post),
            isset($this->business_message) => $this->getUpdateMessageSubType($this->business_message),
            isset($this->edited_business_message) => $this->getUpdateMessageSubType($this->edited_business_message),
            isset($this->business_connection) => 'business_connection',
            isset($this->deleted_business_messages) => 'deleted_business_messages',
            isset($this->message_reaction) => 'message_reaction',
            isset($this->message_reaction_count) => 'message_reaction_count',
            isset($this->inline_query) => 'inline_query',
            isset($this->chosen_inline_result) => 'chosen_inline_result',
            isset($this->callback_query) => 'callback_query',
            isset($this->shipping_query) => 'shipping_query',
            isset($this->pre_checkout_query) => 'pre_checkout_query',
            isset($this->purchased_paid_media) => 'purchased_paid_media',
            isset($this->poll) => 'poll',
            isset($this->poll_answer) => 'poll_answer',
            isset($this->my_chat_member) => 'my_chat_member',
            isset($this->chat_member) => 'chat_member',
            isset($this->chat_join_request) => 'chat_join_request',
            isset($this->chat_boost) => 'chat_boost',
            isset($this->removed_chat_boost) => 'removed_chat_boost',
            default => false
        };
    }

    /**
     * This function returns the type of the message.
     *
     * @param  \LaraGram\Laraquest\Updates\Message|object $message
     * @return string
     */
    public function getUpdateMessageSubType(object $message): string
    {
        return match (true) {
            isset($message->text) => 'text',
            isset($message->animation) => 'animation',
            isset($message->audio) => 'audio',
            isset($message->document) => 'document',
            isset($message->paid_media) => 'paid_media',
            isset($message->photo) => 'photo',
            isset($message->sticker) => 'sticker',
            isset($message->story) => 'story',
            isset($message->video) => 'video',
            isset($message->video_note) => 'video_note',
            isset($message->voice) => 'voice',
            isset($message->checklist) => 'checklist',
            isset($message->contact) => 'contact',
            isset($message->dice) => 'dice',
            isset($message->game) => 'game',
            isset($message->poll) => 'poll',
            isset($message->venue) => 'venue',
            isset($message->location) => 'location',
            isset($message->pinned_message) => 'pinned_message',
            isset($message->invoice) => 'invoice',
            isset($message->successful_payment) => 'successful_payment',
            isset($message->refunded_payment) => 'refunded_payment',
            isset($message->users_shared) => 'users_shared',
            isset($message->chat_shared) => 'chat_shared',
            isset($message->passport_data) => 'passport_data',
            isset($message->proximity_alert_triggered) => 'proximity_alert_triggered',
            isset($message->boost_added) => 'boost_added',
            isset($message->chat_background_set) => 'chat_background_set',
            isset($message->checklist_tasks_done) => 'checklist_tasks_done',
            isset($message->checklist_tasks_added) => 'checklist_tasks_added',
            isset($message->direct_message_price_changed) => 'direct_message_price_changed',
            isset($message->forum_topic_created) => 'forum_topic_created',
            isset($message->forum_topic_edited) => 'forum_topic_edited',
            isset($message->forum_topic_closed) => 'forum_topic_closed',
            isset($message->forum_topic_reopened) => 'forum_topic_reopened',
            isset($message->general_forum_topic_hidden) => 'general_forum_topic_hidden',
            isset($message->general_forum_topic_unhidden) => 'general_forum_topic_unhidden',
            isset($message->giveaway_created) => 'giveaway_created',
            isset($message->giveaway) => 'giveaway',
            isset($message->giveaway_winners) => 'giveaway_winners',
            isset($message->giveaway_completed) => 'giveaway_completed',
            isset($message->paid_message_price_changed) => 'paid_message_price_changed',
            isset($message->video_chat_scheduled) => 'video_chat_scheduled',
            isset($message->video_chat_started) => 'video_chat_started',
            isset($message->video_chat_ended) => 'video_chat_ended',
            isset($message->video_chat_participants_invited) => 'video_chat_participants_invited',
            isset($message->left_chat_member) => 'left_chat_member',
            isset($message->new_chat_members) => 'new_chat_members',
            isset($message->new_chat_title) => 'new_chat_title',
            isset($message->new_chat_photo) => 'new_chat_photo',
            isset($message->delete_chat_photo) => 'delete_chat_photo',
            isset($message->group_chat_created) => 'group_chat_created',
            isset($message->supergroup_chat_created) => 'supergroup_chat_created',
            isset($message->channel_chat_created) => 'channel_chat_created',
            isset($message->message_auto_delete_timer_changed) => 'message_auto_delete_timer_changed',
            default => throw new InvalidUpdateType('Unknown message type')
        };
    }

    /**
     * detect the scope of message.
     *
     * @return string
     */
    public function scope()
    {
        return match (true) {
            isset($this->message->chat->type) => $this->message->chat->type,
            isset($this->edited_message->chat->type) => $this->edited_message->chat->type,
            isset($this->channel_post->chat->type) => $this->channel_post->chat->type,
            isset($this->edited_channel_post->chat->type) => $this->edited_channel_post->chat->type,
            isset($this->business_message->chat->type) => $this->business_message->chat->type,
            isset($this->edited_business_message->chat->type) => $this->edited_business_message->chat->type,
            isset($this->deleted_business_messages->chat->type) => $this->deleted_business_messages->chat->type,
            isset($this->message_reaction->chat->type) => $this->message_reaction->chat->type,
            isset($this->message_reaction_count->chat->type) => $this->message_reaction_count->chat->type,
            isset($this->callback_query->message->chat->type) => $this->callback_query->message->chat->type,
            isset($this->poll_answer->voter_chat->type) => $this->poll_answer->voter_chat->type,
            isset($this->my_chat_member->chat->type) => $this->my_chat_member->chat->type,
            isset($this->chat_member->chat->type) => $this->chat_member->chat->type,
            isset($this->chat_join_request->chat->type) => $this->chat_join_request->chat->type,
            isset($this->chat_boost->chat->type) => $this->chat_boost->chat->type,
            isset($this->removed_chat_boost->chat->type) => $this->removed_chat_boost->chat->type,
            default => null
        };
    }

    /**
     * detect the message is reply or not.
     *
     * @return bool
     */
    public function isReply()
    {
        return match (true) {
            isset($this->message->reply_to_message),
            isset($this->edited_message->reply_to_message),
            isset($this->channel_post->reply_to_message),
            isset($this->edited_channel_post->reply_to_message),
            isset($this->business_message->reply_to_message),
            isset($this->edited_business_message->reply_to_message),
            isset($this->callback_query->message->reply_to_message) => true,
            default => false
        };
    }
}