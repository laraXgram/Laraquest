<?php

namespace LaraGram\Laraquest\Updates;

class Message
{
    public static function message_id(): int
    {

    }

    public static function message_thread_id(): int
    {

    }

    public static function from(): User
    {

    }

    public static function sender_chat(): Chat
    {

    }

    public static function sender_boost_count(): int
    {

    }

    public static function sender_business_bot(): User
    {

    }

    public static function date(): int
    {

    }

    public static function business_connection_id(): string
    {

    }

    public static function chat(): Chat
    {

    }

    public static function forward_origin(): MessageOriginUser|MessageOriginHiddenUser|MessageOriginChat|MessageOriginChannel
    {

    }

    public static function is_topic_message(): true
    {

    }

    public static function is_automatic_forward(): true
    {

    }

    public static function reply_to_message(): Message
    {

    }

    public static function external_reply(): ExternalReplyInfo
    {

    }

    public static function quote(): TextQuote
    {

    }

    public static function reply_to_story(): Story
    {

    }

    public static function via_bot(): User
    {

    }

    public static function edit_date(): int
    {

    }

    public static function has_protected_content(): true
    {

    }

    public static function is_from_offline(): true
    {

    }

    public static function media_group_id(): string
    {

    }

    public static function author_signature(): string
    {

    }

    public static function text(): string
    {

    }

    public static function entities(): array
    {

    }

    public static function link_preview_options(): LinkPreviewOptions
    {

    }

    public static function animation(): Animation
    {

    }

    public static function audio(): Audio
    {

    }

    public static function document(): Document
    {

    }

    public static function photo(): array
    {

    }

    public static function sticker(): Sticker
    {

    }

    public static function story(): Story
    {

    }

    public static function video(): Video
    {

    }

    public static function video_note(): VideoNote
    {

    }

    public static function voice(): Voice
    {

    }

    public static function caption(): string
    {

    }

    public static function caption_entities(): array
    {

    }

    public static function has_media_spoiler(): true
    {

    }

    public static function contact(): Contact
    {

    }

    public static function dice(): Dice
    {

    }

    public static function game(): Game
    {

    }

    public static function poll(): Poll
    {

    }

    public static function venue(): Venue
    {

    }

    public static function location(): Location
    {

    }

    public static function new_chat_members(): array
    {

    }

    public static function left_chat_member(): User
    {

    }

    public static function new_chat_title(): string
    {

    }


    public static function new_chat_photo(): array
    {

    }

    public static function delete_chat_photo(): true
    {

    }

    public static function group_chat_created(): true
    {

    }

    public static function supergroup_chat_created(): true
    {

    }

    public static function channel_chat_created(): true
    {

    }

    public static function message_auto_delete_timer_changed(): MessageAutoDeleteTimerChanged
    {

    }

    public static function migrate_to_chat_id(): int
    {

    }

    public static function migrate_from_chat_id(): int
    {

    }

    public static function pinned_message(): Message|InaccessibleMessage
    {

    }

    public static function invoice(): Invoice
    {

    }

    public static function successful_payment(): SuccessfulPayment
    {

    }

    public static function users_shared(): UsersShared
    {

    }

    public static function chat_shared(): ChatShared
    {

    }

    public static function connected_website(): string
    {

    }

    public static function write_access_allowed(): WriteAccessAllowed
    {

    }

    public static function passport_data(): PassportData
    {

    }

    public static function proximity_alert_triggered(): ProximityAlertTriggered
    {

    }

    public static function boost_added(): ChatBoostAdded
    {

    }

    public static function forum_topic_created(): ForumTopicCreated
    {

    }

    public static function forum_topic_edited(): ForumTopicEdited
    {

    }

    public static function forum_topic_closed(): ForumTopicClosed
    {

    }

    public static function forum_topic_reopened(): ForumTopicReopened
    {

    }

    public static function general_forum_topic_hidden(): GeneralForumTopicHidden
    {

    }

    public static function general_forum_topic_unhidden(): GeneralForumTopicUnhidden
    {

    }

    public static function giveaway_created(): GiveawayCreated
    {

    }

    public static function giveaway(): Giveaway
    {

    }

    public static function giveaway_winners(): GiveawayWinners
    {

    }

    public static function giveaway_completed(): GiveawayCompleted
    {

    }

    public static function video_chat_scheduled(): VideoChatScheduled
    {

    }

    public static function video_chat_started(): VideoChatStarted
    {

    }

    public static function video_chat_ended(): VideoChatEnded
    {

    }

    public static function video_chat_participants_invited(): VideoChatParticipantsInvited
    {

    }

    public static function web_app_data(): WebAppData
    {

    }

    public static function reply_markup(): InlineKeyboardMarkup
    {

    }
}
