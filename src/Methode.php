<?php

namespace LaraGram\Laraquest;

use LaraGram\Laraquest\Connection\Curl;
use LaraGram\Laraquest\Connection\NoResponseCurl;

trait Methode
{
    private int|Mode $mode = 0;
    private $connection = null;

    public function mode(Mode|int $mode): static
    {
        $this->mode = $mode->value ?? $mode;
        return $this;
    }

    public function connection($name)
    {
        $this->connection = $name;
        return $this;
    }

    public function getConnection()
    {
        return $this->connection;
    }

    private function endpoint($method, $params)
    {
        if (class_exists("LaraGram\\Config\\Repository")) {
            $connection_name = $this->connection ?? config('bot.default');
            $update_type = config('laraquest.update_type');
            $token = config('bot.connections.'.$connection_name.'.token');
            $api_server = config('bot.api_server.endpoint');
        } else {
            $update_type = $_ENV['UPDATE_TYPE'];
            $token = $_ENV['CONNECTIONS'][$this->connection ?? 'bot']['BOT_TOKEN'] ?? $_ENV['BOT_TOKEN'];
            $api_server = $_ENV['BOT_API_SERVER'];
        }

        if ($this->mode == 0) {
            $this->mode = match ($update_type) {
                'curl' => 32,
                'no_response_curl' => 64,
                default => 32
            };
        }

        $params = array_filter($params, function ($value) {
            return !is_null($value);
        });

        foreach ($params as $key => $value) {
            if (gettype($value) == 'object') {
                $params[$key] = json_encode($value);
            }
        }

        if ($this->mode == 32) {
            return (new Curl($token, $api_server))->endpoint($method, $params);
        } elseif ($this->mode = 64) {
            return (new NoResponseCurl($token, $api_server))->endpoint($method, $params);
        }

        return false;
    }

    public function getUpdates($offset = null, $limit = null, $timeout = null, $allowed_updates = null)
    {
        return $this->endpoint('getUpdates', get_defined_vars());
    }

    public function setWebhook($url, $certificate = null, $ip_address = null, $max_connections = null, $allowed_updates = null, $drop_pending_updates = null, $secret_token = null)
    {
        return $this->endpoint('setWebhook', get_defined_vars());
    }

    public function deleteWebhook($drop_pending_updates = null)
    {
        return $this->endpoint('deleteWebhook', get_defined_vars());
    }

    public function getWebhookInfo()
    {
        return $this->endpoint('getWebhookInfo', get_defined_vars());
    }

    public function getMe()
    {
        return $this->endpoint('getMe', get_defined_vars());
    }

    public function logOut()
    {
        return $this->endpoint('logOut', get_defined_vars());
    }

    public function close()
    {
        return $this->endpoint('close', get_defined_vars());
    }

    public function sendMessage($chat_id, $text, $parse_mode = null, $message_thread_id = null, $direct_messages_topic_id = null, $reply_parameters = null, $reply_markup = null, $protect_content = null, $disable_notification = null, $link_preview_options = null, $entities = null, $business_connection_id = null, $message_effect_id = null, $allow_paid_broadcast = null, $suggested_post_parameters = null)
    {
        return $this->endpoint('sendMessage', get_defined_vars());
    }

    public function forwardMessage($chat_id, $from_chat_id, $message_id, $video_start_timestamp = null, $message_thread_id = null, $direct_messages_topic_id = null, $protect_content = null, $disable_notification = null, $suggested_post_parameters = null, $message_effect_id = null)
    {
        return $this->endpoint('forwardMessage', get_defined_vars());
    }

    public function forwardMessages($chat_id, $from_chat_id, $message_ids, $message_thread_id = null, $direct_messages_topic_id = null, $protect_content = null, $disable_notification = null)
    {
        return $this->endpoint('forwardMessages', get_defined_vars());
    }

    public function copyMessage($chat_id, $from_chat_id, $message_id, $video_start_timestamp = null, $parse_mode = null, $message_thread_id = null, $direct_messages_topic_id = null, $reply_parameters = null, $reply_markup = null, $protect_content = null, $disable_notification = null, $caption = null, $caption_entities = null, $show_caption_above_media = null, $allow_paid_broadcast = null, $suggested_post_parameters = null, $message_effect_id = null)
    {
        return $this->endpoint('copyMessage', get_defined_vars());
    }

    public function copyMessages($chat_id, $from_chat_id, $message_ids, $message_thread_id = null, $direct_messages_topic_id = null, $protect_content = null, $disable_notification = null, $remove_caption = null)
    {
        return $this->endpoint('copyMessages', get_defined_vars());
    }

    public function sendPhoto($chat_id, $photo, $caption = null, $parse_mode = null, $message_thread_id = null, $direct_messages_topic_id = null, $reply_parameters = null, $reply_markup = null, $protect_content = null, $disable_notification = null, $has_spoiler = null, $caption_entities = null, $show_caption_above_media = null, $message_effect_id = null, $business_connection_id = null, $allow_paid_broadcast = null, $suggested_post_parameters = null)
    {
        return $this->endpoint('sendPhoto', get_defined_vars());
    }

    public function sendAudio($chat_id, $audio, $caption = null, $parse_mode = null, $message_thread_id = null, $direct_messages_topic_id = null, $duration = null, $performer = null, $title = null, $thumbnail = null, $reply_parameters = null, $reply_markup = null, $protect_content = null, $disable_notification = null, $caption_entities = null, $business_connection_id = null, $message_effect_id = null, $allow_paid_broadcast = null, $suggested_post_parameters = null)
    {
        return $this->endpoint('sendAudio', get_defined_vars());
    }

    public function sendDocument($chat_id, $document, $caption = null, $parse_mode = null, $message_thread_id = null, $direct_messages_topic_id = null, $thumbnail = null, $reply_parameters = null, $reply_markup = null, $protect_content = null, $disable_notification = null, $caption_entities = null, $business_connection_id = null, $disable_content_type_detection = null, $message_effect_id = null, $allow_paid_broadcast = null, $suggested_post_parameters = null)
    {
        return $this->endpoint('sendDocument', get_defined_vars());
    }

    public function sendVideo($chat_id, $video, $caption = null, $parse_mode = null, $message_thread_id = null, $direct_messages_topic_id = null, $duration = null, $width = null, $height = null, $thumbnail = null, $cover = null, $start_timestamp = null, $reply_parameters = null, $reply_markup = null, $protect_content = null, $disable_notification = null, $caption_entities = null, $show_caption_above_media = null, $has_spoiler = null, $supports_streaming = null, $business_connection_id = null, $message_effect_id = null, $allow_paid_broadcast = null, $suggested_post_parameters = null)
    {
        return $this->endpoint('sendVideo', get_defined_vars());
    }

    public function sendAnimation($chat_id, $animation, $caption = null, $parse_mode = null, $message_thread_id = null, $direct_messages_topic_id = null, $duration = null, $width = null, $height = null, $thumbnail = null, $reply_parameters = null, $reply_markup = null, $protect_content = null, $disable_notification = null, $caption_entities = null, $show_caption_above_media = null, $has_spoiler = null, $business_connection_id = null, $message_effect_id = null, $allow_paid_broadcast = null, $suggested_post_parameters = null)
    {
        return $this->endpoint('sendAnimation', get_defined_vars());
    }

    public function sendVoice($chat_id, $voice, $caption = null, $parse_mode = null, $message_thread_id = null, $direct_messages_topic_id = null, $duration = null, $reply_parameters = null, $reply_markup = null, $protect_content = null, $disable_notification = null, $caption_entities = null, $business_connection_id = null, $message_effect_id = null, $allow_paid_broadcast = null)
    {
        return $this->endpoint('sendVoice', get_defined_vars());
    }

    public function sendVideoNote($chat_id, $video_note, $message_thread_id = null, $direct_messages_topic_id = null, $duration = null, $length = null, $thumbnail = null, $reply_parameters = null, $reply_markup = null, $protect_content = null, $disable_notification = null, $business_connection_id = null, $message_effect_id = null, $allow_paid_broadcast = null, $suggested_post_parameters = null)
    {
        return $this->endpoint('sendVideoNote', get_defined_vars());
    }

    public function sendPaidMedia($chat_id, $star_count, $media, $payload = null, $caption = null, $pars_mode = null, $caption_entities = null, $show_caption_above_media = null, $disable_notification = null, $protect_content = null, $reply_parameters = null, $reply_markup = null, $business_connection_id = null, $direct_messages_topic_id = null, $allow_paid_broadcast = null, $suggested_post_parameters = null, $message_thread_id = null)
    {
        return $this->endpoint('sendPaidMedia', get_defined_vars());
    }

    public function sendMediaGroup($chat_id, $media, $message_thread_id = null, $direct_messages_topic_id = null, $reply_parameters = null, $protect_content = null, $disable_notification = null, $business_connection_id = null, $message_effect_id = null, $allow_paid_broadcast = null)
    {
        return $this->endpoint('sendMediaGroup', get_defined_vars());
    }

    public function sendLocation($chat_id, $latitude, $longitude, $horizontal_accuracy = null, $live_period = null, $heading = null, $proximity_alert_radius = null, $message_thread_id = null, $direct_messages_topic_id = null, $reply_parameters = null, $reply_markup = null, $protect_content = null, $disable_notification = null, $business_connection_id = null, $message_effect_id = null, $allow_paid_broadcast = null, $suggested_post_parameters = null)
    {
        return $this->endpoint('sendLocation', get_defined_vars());
    }

    public function sendVenue($chat_id, $latitude, $longitude, $title, $address, $foursquare_id = null, $foursquare_type = null, $google_place_id = null, $google_place_type = null, $message_thread_id = null, $direct_messages_topic_id = null, $reply_parameters = null, $reply_markup = null, $protect_content = null, $disable_notification = null, $business_connection_id = null, $message_effect_id = null, $allow_paid_broadcast = null, $suggested_post_parameters = null)
    {
        return $this->endpoint('sendVenue', get_defined_vars());
    }

    public function sendContact($chat_id, $phone_number, $first_name, $last_name = null, $vcard = null, $message_thread_id = null, $direct_messages_topic_id = null, $reply_parameters = null, $reply_markup = null, $protect_content = null, $disable_notification = null, $business_connection_id = null, $message_effect_id = null, $allow_paid_broadcast = null, $suggested_post_parameters = null)
    {
        return $this->endpoint('sendContact', get_defined_vars());
    }

    public function sendPoll($chat_id, $question, $options, $is_anonymous = null, $type = null, $allows_multiple_answers = null, $question_parse_mode = null, $question_entities = null, $correct_option_id = null, $explanation = null, $explanation_parse_mode = null, $explanation_entities = null, $open_period = null, $close_date = null, $is_closed = null, $message_thread_id = null, $reply_parameters = null, $reply_markup = null, $protect_content = null, $disable_notification = null, $business_connection_id = null, $message_effect_id = null, $allow_paid_broadcast = null)
    {
        return $this->endpoint('sendPoll', get_defined_vars());
    }

    public function sendDice($chat_id, $emoji = null, $message_thread_id = null, $direct_messages_topic_id = null, $reply_parameters = null, $reply_markup = null, $protect_content = null, $disable_notification = null, $business_connection_id = null, $message_effect_id = null, $allow_paid_broadcast = null, $suggested_post_parameters = null)
    {
        return $this->endpoint('sendDice', get_defined_vars());
    }

    public function sendChatAction($chat_id, $action, $message_thread_id = null, $business_connection_id = null)
    {
        return $this->endpoint('sendChatAction', get_defined_vars());
    }

    public function setMessageReaction($chat_id, $message_id, $reaction = null, $is_big = null)
    {
        return $this->endpoint('setMessageReaction', get_defined_vars());
    }

    public function getUserProfilePhotos($user_id, $offset = null, $limit = null)
    {
        return $this->endpoint('getUserProfilePhotos', get_defined_vars());
    }

    public function getFile($file_id)
    {
        return $this->endpoint('getFile', get_defined_vars());
    }

    public function banChatMember($chat_id, $user_id, $until_date = null, $revoke_messages = null)
    {
        return $this->endpoint('banChatMember', get_defined_vars());
    }

    public function unbanChatMember($chat_id, $user_id, $only_if_banned = null)
    {
        return $this->endpoint('unbanChatMember', get_defined_vars());
    }

    public function restrictChatMember($chat_id, $user_id, $permissions, $until_date = null, $use_independent_chat_permissions = null)
    {
        return $this->endpoint('restrictChatMember', get_defined_vars());
    }

    public function promoteChatMember($chat_id, $user_id, $is_anonymous = null, $can_manage_chat = null, $can_delete_messages = null, $can_manage_video_chats = null, $can_restrict_members = null, $can_promote_members = null, $can_change_info = null, $can_invite_users = null, $can_post_stories = null, $can_edit_stories = null, $can_delete_stories = null, $can_post_messages = null, $can_edit_messages = null, $can_pin_messages = null, $can_manage_topics = null, $can_manage_direct_messages = null)
    {
        return $this->endpoint('promoteChatMember', get_defined_vars());
    }

    public function setChatAdministratorCustomTitle($chat_id, $user_id, $title)
    {
        return $this->endpoint('setChatAdministratorCustomTitle', get_defined_vars());
    }

    public function banChatSenderChat($chat_id, $sender_chat_id)
    {
        return $this->endpoint('banChatSenderChat', get_defined_vars());
    }

    public function unbanChatSenderChat($chat_id, $sender_chat_id)
    {
        return $this->endpoint('unbanChatSenderChat', get_defined_vars());
    }

    public function setChatPermissions($chat_id, $permissions, $use_independent_chat_permissions = null)
    {
        return $this->endpoint('setChatPermissions', get_defined_vars());
    }

    public function exportChatInviteLink($chat_id)
    {
        return $this->endpoint('exportChatInviteLink', get_defined_vars());
    }

    public function createChatInviteLink($chat_id, $name = null, $expire_date = null, $member_limit = null, $creates_join_request = null)
    {
        return $this->endpoint('createChatInviteLink', get_defined_vars());
    }

    public function editChatInviteLink($chat_id, $invite_link, $name = null, $expire_date = null, $member_limit = null, $creates_join_request = null)
    {
        return $this->endpoint('editChatInviteLink', get_defined_vars());
    }

    public function revokeChatInviteLink($chat_id, $invite_link)
    {
        return $this->endpoint('revokeChatInviteLink', get_defined_vars());
    }

    public function approveChatJoinRequest($chat_id, $user_id)
    {
        return $this->endpoint('approveChatJoinRequest', get_defined_vars());
    }

    public function declineChatJoinRequest($chat_id, $user_id)
    {
        return $this->endpoint('declineChatJoinRequest', get_defined_vars());
    }

    public function setChatPhoto($chat_id, $photo)
    {
        return $this->endpoint('setChatPhoto', get_defined_vars());
    }

    public function deleteChatPhoto($chat_id)
    {
        return $this->endpoint('deleteChatPhoto', get_defined_vars());
    }

    public function setChatTitle($chat_id, $title)
    {
        return $this->endpoint('setChatTitle', get_defined_vars());
    }

    public function setChatDescription($chat_id, $description)
    {
        return $this->endpoint('setChatDescription', get_defined_vars());
    }

    public function pinChatMessage($chat_id, $message_id, $disable_notification = null, $business_connection_id = null)
    {
        return $this->endpoint('pinChatMessage', get_defined_vars());
    }

    public function unpinChatMessage($chat_id, $message_id, $business_connection_id = null)
    {
        return $this->endpoint('unpinChatMessage', get_defined_vars());
    }

    public function unpinAllChatMessages($chat_id)
    {
        return $this->endpoint('unpinAllChatMessages', get_defined_vars());
    }

    public function leaveChat($chat_id)
    {
        return $this->endpoint('leaveChat', get_defined_vars());
    }

    public function getChat($chat_id)
    {
        return $this->endpoint('getChat', get_defined_vars());
    }

    public function getChatAdministrators($chat_id)
    {
        return $this->endpoint('getChatAdministrators', get_defined_vars());
    }

    public function getChatMemberCount($chat_id)
    {
        return $this->endpoint('getChatMemberCount', get_defined_vars());
    }

    public function getChatMember($chat_id, $user_id)
    {
        return $this->endpoint('getChatMember', get_defined_vars());
    }

    public function setChatStickerSet($chat_id, $sticker_set_name)
    {
        return $this->endpoint('setChatStickerSet', get_defined_vars());
    }

    public function deleteChatStickerSet($chat_id)
    {
        return $this->endpoint('deleteChatStickerSet', get_defined_vars());
    }

    public function getForumTopicIconStickers()
    {
        return $this->endpoint('getForumTopicIconStickers', get_defined_vars());
    }

    public function createForumTopic($chat_id, $name, $icon_color = null, $icon_custom_emoji_id = null)
    {
        return $this->endpoint('createForumTopic', get_defined_vars());
    }

    public function editForumTopic($chat_id, $message_thread_id, $name = null, $icon_custom_emoji_id = null)
    {
        return $this->endpoint('editForumTopic', get_defined_vars());
    }

    public function closeForumTopic($chat_id, $message_thread_id)
    {
        return $this->endpoint('closeForumTopic', get_defined_vars());
    }

    public function reopenForumTopic($chat_id, $message_thread_id)
    {
        return $this->endpoint('reopenForumTopic', get_defined_vars());
    }

    public function deleteForumTopic($chat_id, $message_thread_id)
    {
        return $this->endpoint('deleteForumTopic', get_defined_vars());
    }

    public function unpinAllForumTopicMessages($chat_id, $message_thread_id)
    {
        return $this->endpoint('unpinAllForumTopicMessages', get_defined_vars());
    }

    public function editGeneralForumTopic($chat_id, $name)
    {
        return $this->endpoint('editGeneralForumTopic', get_defined_vars());
    }

    public function closeGeneralForumTopic($chat_id)
    {
        return $this->endpoint('closeGeneralForumTopic', get_defined_vars());
    }

    public function reopenGeneralForumTopic($chat_id)
    {
        return $this->endpoint('reopenGeneralForumTopic', get_defined_vars());
    }

    public function hideGeneralForumTopic($chat_id)
    {
        return $this->endpoint('hideGeneralForumTopic', get_defined_vars());
    }

    public function unhideGeneralForumTopic($chat_id)
    {
        return $this->endpoint('unhideGeneralForumTopic', get_defined_vars());
    }

    public function unpinAllGeneralForumTopicMessages($chat_id)
    {
        return $this->endpoint('unpinAllGeneralForumTopicMessages', get_defined_vars());
    }

    public function answerCallbackQuery($callback_query_id, $text = null, $show_alert = null, $url = null, $cache_time = null)
    {
        return $this->endpoint('answerCallbackQuery', get_defined_vars());
    }

    public function getUserChatBoosts($chat_id, $user_id)
    {
        return $this->endpoint('getUserChatBoosts', get_defined_vars());
    }

    public function getBusinessConnection($business_connection_id)
    {
        return $this->endpoint('getBusinessConnection', get_defined_vars());
    }

    public function setMyCommands($commands, $scope = null, $language_code = null)
    {
        return $this->endpoint('setMyCommands', get_defined_vars());
    }

    public function deleteMyCommands($scope = null, $language_code = null)
    {
        return $this->endpoint('deleteMyCommands', get_defined_vars());
    }

    public function getMyCommands($scope = null, $language_code = null)
    {
        return $this->endpoint('getMyCommands', get_defined_vars());
    }

    public function setMyName($name = null, $language_code = null)
    {
        return $this->endpoint('setMyName', get_defined_vars());
    }

    public function getMyName($language_code = null)
    {
        return $this->endpoint('getMyName', get_defined_vars());
    }

    public function setMyDescription($description = null, $language_code = null)
    {
        return $this->endpoint('setMyDescription', get_defined_vars());
    }

    public function getMyDescription($language_code = null)
    {
        return $this->endpoint('getMyDescription', get_defined_vars());
    }

    public function setMyShortDescription($short_description = null, $language_code = null)
    {
        return $this->endpoint('setMyShortDescription', get_defined_vars());
    }

    public function getMyShortDescription($language_code = null)
    {
        return $this->endpoint('getMyShortDescription', get_defined_vars());
    }

    public function setChatMenuButton($chat_id = null, $menu_button = null)
    {
        return $this->endpoint('setChatMenuButton', get_defined_vars());
    }

    public function getChatMenuButton($chat_id = null)
    {
        return $this->endpoint('getChatMenuButton', get_defined_vars());
    }

    public function setMyDefaultAdministratorRights($rights = null, $for_channels = null)
    {
        return $this->endpoint('setMyDefaultAdministratorRights', get_defined_vars());
    }

    public function getMyDefaultAdministratorRights($for_channels = null)
    {
        return $this->endpoint('getMyDefaultAdministratorRights', get_defined_vars());
    }

    public function editMessageText($text, $chat_id = null, $message_id = null, $inline_message_id = null, $parse_mode = null, $entities = null, $link_preview_options = null, $reply_markup = null, $business_connection_id = null)
    {
        return $this->endpoint('editMessageText', get_defined_vars());
    }

    public function editMessageCaption($chat_id = null, $message_id = null, $inline_message_id = null, $caption = null, $parse_mode = null, $caption_entities = null, $reply_markup = null, $show_caption_above_media = null, $business_connection_id = null)
    {
        return $this->endpoint('editMessageCaption', get_defined_vars());
    }

    public function editMessageMedia($media, $chat_id = null, $message_id = null, $inline_message_id = null, $reply_markup = null, $business_connection_id = null)
    {
        return $this->endpoint('editMessageMedia', get_defined_vars());
    }

    public function editMessageLiveLocation($latitude, $longitude, $chat_id = null, $message_id = null, $inline_message_id = null, $horizontal_accuracy = null, $heading = null, $proximity_alert_radius = null, $reply_markup = null, $live_period = null, $business_connection_id = null)
    {
        return $this->endpoint('editMessageLiveLocation', get_defined_vars());
    }

    public function stopMessageLiveLocation($chat_id = null, $message_id = null, $inline_message_id = null, $reply_markup = null, $business_connection_id = null)
    {
        return $this->endpoint('stopMessageLiveLocation', get_defined_vars());
    }

    public function editMessageReplyMarkup($chat_id = null, $message_id = null, $inline_message_id = null, $reply_markup = null, $business_connection_id = null)
    {
        return $this->endpoint('editMessageReplyMarkup', get_defined_vars());
    }

    public function stopPoll($chat_id, $message_id, $reply_markup = null, $business_connection_id = null)
    {
        return $this->endpoint('stopPoll', get_defined_vars());
    }

    public function deleteMessage($chat_id, $message_id)
    {
        return $this->endpoint('deleteMessage', get_defined_vars());
    }

    public function deleteMessages($chat_id, $message_ids)
    {
        return $this->endpoint('deleteMessages', get_defined_vars());
    }

    public function sendSticker($chat_id, $sticker, $emoji = null, $message_thread_id = null, $direct_messages_topic_id = null, $reply_parameters = null, $reply_markup = null, $protect_content = null, $disable_notification = null, $business_connection_id = null, $message_effect_id = null, $allow_paid_broadcast = null, $suggested_post_parameters = null)
    {
        return $this->endpoint('sendSticker', get_defined_vars());
    }

    public function getStickerSet($name)
    {
        return $this->endpoint('getStickerSet', get_defined_vars());
    }

    public function getCustomEmojiStickers($custom_emoji_ids)
    {
        return $this->endpoint('getCustomEmojiStickers', get_defined_vars());
    }

    public function uploadStickerFile($user_id, $sticker, $sticker_format)
    {
        return $this->endpoint('uploadStickerFile', get_defined_vars());
    }

    public function createNewStickerSet($user_id, $name, $title, $stickers, $sticker_type = null, $needs_repainting = null)
    {
        return $this->endpoint('createNewStickerSet', get_defined_vars());
    }

    public function addStickerToSet($user_id, $name, $sticker)
    {
        return $this->endpoint('addStickerToSet', get_defined_vars());
    }

    public function setStickerPositionInSet($sticker, $position)
    {
        return $this->endpoint('setStickerPositionInSet', get_defined_vars());
    }

    public function deleteStickerFromSet($sticker)
    {
        return $this->endpoint('deleteStickerFromSet', get_defined_vars());
    }

    public function replaceStickerInSet($user_id, $name, $old_sticker, $sticker)
    {
        return $this->endpoint('replaceStickerInSet', get_defined_vars());
    }

    public function setStickerEmojiList($sticker, $emoji_list)
    {
        return $this->endpoint('setStickerEmojiList', get_defined_vars());
    }

    public function setStickerKeywords($sticker, $keywords = null)
    {
        return $this->endpoint('setStickerKeywords', get_defined_vars());
    }

    public function setStickerMaskPosition($sticker, $mask_position = null)
    {
        return $this->endpoint('setStickerMaskPosition', get_defined_vars());
    }

    public function setStickerSetTitle($name, $title)
    {
        return $this->endpoint('setStickerSetTitle', get_defined_vars());
    }

    public function setStickerSetThumbnail($name, $user_id, $format, $thumbnail = null)
    {
        return $this->endpoint('setStickerSetThumbnail', get_defined_vars());
    }

    public function setCustomEmojiStickerSetThumbnail($name, $custom_emoji_id = null)
    {
        return $this->endpoint('setCustomEmojiStickerSetThumbnail', get_defined_vars());
    }

    public function deleteStickerSet($name)
    {
        return $this->endpoint('deleteStickerSet', get_defined_vars());
    }

    public function answerInlineQuery($inline_query_id, $results, $cache_time = null, $is_personal = null, $next_offset = null, $button = null)
    {
        return $this->endpoint('answerInlineQuery', get_defined_vars());
    }

    public function answerWebAppQuery($web_app_query_id, $result)
    {
        return $this->endpoint('answerWebAppQuery', get_defined_vars());
    }

    public function sendInvoice($chat_id, $title, $description, $payload, $provider_token, $currency, $price, $message_thread_id = null, $direct_messages_topic_id = null, $max_tip_amount = null, $suggested_tip_amounts = null, $start_parameter = null, $provider_data = null, $photo_url = null, $photo_size = null, $photo_width = null, $photo_height = null, $need_name = null, $need_phone_number = null, $need_email = null, $need_shipping_address = null, $send_phone_number_to_provider = null, $send_email_to_provider = null, $is_flexible = null, $disable_notification = null, $protect_content = null, $reply_parameters = null, $reply_markup = null, $message_effect_id = null, $allow_paid_broadcast = null, $suggested_post_parameters = null)
    {
        return $this->endpoint('sendInvoice', get_defined_vars());
    }

    public function createInvoiceLink($title, $description, $payload, $provider_token, $currency, $price, $subscription_period = null, $max_tip_amount = null, $suggested_tip_amounts = null, $provider_data = null, $photo_url = null, $photo_size = null, $photo_width = null, $photo_height = null, $need_name = null, $need_phone_number = null, $need_email = null, $need_shipping_address = null, $send_phone_number_to_provider = null, $send_email_to_provider = null, $is_flexible = null, $business_connection_id = null)
    {
        return $this->endpoint('createInvoiceLink', get_defined_vars());
    }

    public function answerShippingQuery($shipping_query_id, $ok, $shipping_options = null, $error_message = null)
    {
        return $this->endpoint('answerShippingQuery', get_defined_vars());
    }

    public function answerPreCheckoutQuery($pre_checkout_query_id, $ok, $error_message = null)
    {
        return $this->endpoint('answerPreCheckoutQuery', get_defined_vars());
    }

    public function getStarTransactions($offset = null, $limit = null)
    {
        return $this->endpoint('getStarTransactions', get_defined_vars());
    }

    public function refundStarPayment($user_id, $telegram_payment_charge_id)
    {
        return $this->endpoint('answerPreCheckoutQuery', get_defined_vars());
    }

    public function setPassportDataErrors($user_id, $errors)
    {
        return $this->endpoint('setPassportDataErrors', get_defined_vars());
    }

    public function sendGame($chat_id, $game_short_name, $message_thread_id = null, $disable_notification = null, $protect_content = null, $reply_parameters = null, $reply_markup = null, $business_connection_id = null, $message_effect_id = null, $allow_paid_broadcast = null)
    {
        return $this->endpoint('sendGame', get_defined_vars());
    }

    public function setGameScore($user_id, $score, $chat_id = null, $message_id = null, $inline_message_id = null, $disable_edit_message = null, $force = null)
    {
        return $this->endpoint('setGameScore', get_defined_vars());
    }

    public function getGameHighScores($user_id, $chat_id = null, $message_id = null, $inline_message_id = null)
    {
        return $this->endpoint('getGameHighScores', get_defined_vars());
    }

    public function createChatSubscriptionInviteLink($chat_id, $subscription_period, $subscription_price, $name = null)
    {
        return $this->endpoint('createChatSubscriptionInviteLink', get_defined_vars());
    }

    public function editChatSubscriptionInviteLink($chat_id, $invite_link, $name = null)
    {
        return $this->endpoint('editChatSubscriptionInviteLink', get_defined_vars());
    }

    public function editUserStarSubscription($user_id, $telegram_payment_charge_id, $is_canceled)
    {
        return $this->endpoint('editUserStarSubscription', get_defined_vars());
    }

    public function setUserEmojiStatus($user_id, $emoji_status_custom_emoji_id = null, $emoji_status_expiration_date = null)
    {
        return $this->endpoint('setUserEmojiStatus', get_defined_vars());
    }

    public function savePreparedInlineMessage($user_id, $result, $allow_user_chats = null, $allow_bot_chats = null, $allow_group_chats = null, $allow_channel_chats = null)
    {
        return $this->endpoint('savePreparedInlineMessage', get_defined_vars());
    }

    public function getAvailableGifts()
    {
        return $this->endpoint('getAvailableGifts', get_defined_vars());
    }

    public function sendGift($user_id, $gift_id, $chat_id = null, $pay_for_upgrade = null, $text = null, $text_parse_mode = null, $text_entities = null)
    {
        return $this->endpoint('sendGift', get_defined_vars());
    }

    public function verifyUser($user_id, $custom_description = null)
    {
        return $this->endpoint('verifyUser', get_defined_vars());
    }

    public function verifyChat($chat_id, $custom_description = null)
    {
        return $this->endpoint('verifyChat', get_defined_vars());
    }

    public function removeUserVerification($user_id)
    {
        return $this->endpoint('removeUserVerification', get_defined_vars());
    }

    public function removeChatVerification($chat_id)
    {
        return $this->endpoint('removeChatVerification', get_defined_vars());
    }

    public function deleteBusinessMessages($business_connection_id, $message_ids)
    {
        return $this->endpoint('deleteBusinessMessages', get_defined_vars());
    }

    public function setBusinessAccountName($business_connection_id, $first_name, $last_name = null)
    {
        return $this->endpoint('setBusinessAccountName', get_defined_vars());
    }

    public function setBusinessAccountUsername($business_connection_id, $username = null)
    {
        return $this->endpoint('setBusinessAccountUsername', get_defined_vars());
    }

    public function setBusinessAccountBio($business_connection_id, $bio = null)
    {
        return $this->endpoint('setBusinessAccountBio', get_defined_vars());
    }

    public function setBusinessAccountProfilePhoto($business_connection_id, $photo, $is_public = null)
    {
        return $this->endpoint('setBusinessAccountProfilePhoto', get_defined_vars());
    }

    public function removeBusinessAccountProfilePhoto($business_connection_id, $is_public = null)
    {
        return $this->endpoint('removeBusinessAccountProfilePhoto', get_defined_vars());
    }

    public function setBusinessAccountGiftSettings($business_connection_id, $show_gift_button, $accepted_gift_types)
    {
        return $this->endpoint('setBusinessAccountGiftSettings', get_defined_vars());
    }

    public function getBusinessAccountStarBalance($business_connection_id)
    {
        return $this->endpoint('getBusinessAccountStarBalance', get_defined_vars());
    }

    public function transferBusinessAccountStars($business_connection_id, $star_count)
    {
        return $this->endpoint('transferBusinessAccountStars', get_defined_vars());
    }

    public function getBusinessAccountGifts($business_connection_id, $exclude_unsaved = null, $exclude_saved = null, $exclude_unlimited = null, $exclude_limited_upgradable = null, $exclude_limited_non_upgradable = null, $exclude_unique = null, $exclude_from_blockchain = null, $sort_by_price = null, $offset = null, $limit = null)
    {
        return $this->endpoint('getBusinessAccountGifts', get_defined_vars());
    }

    public function convertGiftToStars($business_connection_id, $owned_gift_id)
    {
        return $this->endpoint('convertGiftToStars', get_defined_vars());
    }

    public function upgradeGift($business_connection_id, $owned_gift_id, $keep_original_details = null, $star_count = null)
    {
        return $this->endpoint('upgradeGift', get_defined_vars());
    }

    public function transferGift($business_connection_id, $owned_gift_id, $new_owner_chat_id, $star_count = null)
    {
        return $this->endpoint('transferGift', get_defined_vars());
    }

    public function postStory($business_connection_id, $content, $active_period, $caption = null, $parse_mode = null, $caption_entities = null, $areas = null, $post_to_chat_page = null, $protect_content = null)
    {
        return $this->endpoint('postStory', get_defined_vars());
    }

    public function editStory($business_connection_id, $story_id, $content, $caption = null, $parse_mode = null, $caption_entities = null, $areas = null)
    {
        return $this->endpoint('editStory', get_defined_vars());
    }

    public function deleteStory($business_connection_id, $story_id)
    {
        return $this->endpoint('deleteStory', get_defined_vars());
    }

    public function repostStory($business_connection_id, $from_chat_id, $from_story_id, $active_period, $post_to_chat_page = null, $protect_content = null)
    {
        return $this->endpoint('repostStory', get_defined_vars());
    }

    public function giftPremiumSubscription($user_id, $month_count, $star_count, $text = null, $text_parse_mode = null, $text_entities = null)
    {
        return $this->endpoint('giftPremiumSubscription', get_defined_vars());
    }

    public function sendChecklist($business_connection_id, $chat_id, $checklist, $disable_notification = null, $protect_content = null, $message_effect_id = null, $reply_parameters = null, $reply_markup = null)
    {
        return $this->endpoint('sendChecklist', get_defined_vars());
    }

    public function editMessageChecklist($business_connection_id, $chat_id, $message_id, $checklist, $reply_markup = null)
    {
        return $this->endpoint('editMessageChecklist', get_defined_vars());
    }

    public function getMyStarBalance()
    {
        return $this->endpoint('getMyStarBalance', get_defined_vars());
    }

    public function approveSuggestedPost($chat_id, $message_id, $send_date = null)
    {
        return $this->endpoint('approveSuggestedPost', get_defined_vars());
    }

    public function declineSuggestedPost($chat_id, $message_id, $comment = null)
    {
        return $this->endpoint('declineSuggestedPost', get_defined_vars());
    }

    public function sendMessageDraft($chat_id, $draft_id, $text, $parse_mode = null, $message_thread_id = null, $entities = null)
    {
        return $this->endpoint('sendMessageDraft', get_defined_vars());
    }

    public function getUserGifts($chat_id, $exclude_unlimited = null, $exclude_limited_upgradable = null, $exclude_limited_non_upgradable = null, $exclude_from_blockchain = null, $exclude_unique = null, $sort_by_price = null, $offset = null, $limit = null)
    {
        return $this->endpoint('getUserGifts', get_defined_vars());
    }

    public function getChatGifts($chat_id, $exclude_unsaved = null, $exclude_saved = null, $exclude_unlimited = null, $exclude_limited_upgradable = null, $exclude_limited_non_upgradable = null, $exclude_from_blockchain = null, $exclude_unique = null, $sort_by_price = null, $offset = null, $limit = null)
    {
        return $this->endpoint('getChatGifts', get_defined_vars());
    }
}

