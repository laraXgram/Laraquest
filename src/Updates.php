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
 * @property Poll $poll
 * @property PollAnswer $poll_answer
 * @property ChatMemberUpdated $my_chat_member
 * @property ChatMemberUpdated $chat_member
 * @property ChatJoinRequest $chat_join_request
 * @property ChatBoostUpdated $chat_boost
 * @property ChatBoostRemoved $removed_chat_boost
 * @property PaidMediaPurchased $purchased_paid_media
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
        global $data;
        global $swoole;
        $update = match ($this->update_type){
            'sync' => json_decode(file_get_contents('php://input')),
            'global' => json_decode($data['argv'][1]),
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
        global $data;
        global $swoole;
        return match ($this->update_type){
            'sync' => json_decode(file_get_contents('php://input')),
            'global' => json_decode($data['argv'][1]),
            'openswoole', 'swoole' => $swoole,
            'polling' => $data,
            default => throw new InvalidGetUpdateType("Unknown get update type")
        };
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
    public function getUpdateMessageSubType(object $message): string
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