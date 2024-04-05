<?php

namespace LaraGram\Laraquest;

use LaraGram\Laraquest\Connection\AMP;
use LaraGram\Laraquest\Connection\Curl;
use LaraGram\Laraquest\Connection\NoResponseCurl;

trait Methode
{
    private int|Mode $mode = 32;

    public function mode(Mode $mode): static
    {
        $this->mode = $mode->value;
        return $this;
    }

    private function endpoint($method, $params)
    {
        $params= array_filter($params, function($value) {
            return !is_null($value);
        });

        if ($this->mode == 32) {
            return (new Curl($_ENV['BOT_TOKEN'], $_ENV['BOT_API_SERVER']))->endpoint($method, $params);
        } elseif ($this->mode = 64) {
            return (new NoResponseCurl($_ENV['BOT_TOKEN'], $_ENV['BOT_API_SERVER']))->endpoint($method, $params);
        } elseif ($this->mode = 128) {
            return (new AMP($_ENV['BOT_TOKEN'], $_ENV['BOT_API_SERVER']))->endpoint($method, $params);
        } elseif ($this->mode = 256) {
//            throw new Exception('mode OPENSWOOLE not exist yet!');
        }
    }

    public function getMe(): bool|string|array|null
    {
        return $this->endpoint('getMe', get_defined_vars());
    }

    public function logOut(): bool|string|array|null
    {
        return $this->endpoint('logOut', get_defined_vars());
    }

    public function close(): bool|string|array|null
    {
        return $this->endpoint('close', get_defined_vars());
    }

    public function sendMessage($chat_id, $text, $parse_mode = null, $message_thread_id = null, $reply_parameters = null, $reply_markup = null, $protect_content = null, $disable_notification = null, $link_preview_options = null, $entities = null, $business_connection_id = null): bool|array|null
    {
        return $this->endpoint('sendMessage', get_defined_vars());
    }

    public function forwardMessage($chat_id, $from_chat_id, $message_id, $message_thread_id = null, $protect_content = null, $disable_notification = null): bool|string|array|null
    {
        return $this->endpoint('forwardMessage', get_defined_vars());
    }

    public function forwardMessages($chat_id, $from_chat_id, $message_ids, $message_thread_id = null, $protect_content = null, $disable_notification = null): bool|string|array|null
    {
        return $this->endpoint('forwardMessages', get_defined_vars());
    }

    public function copyMessage($chat_id, $from_chat_id, $message_id, $parse_mode = null, $message_thread_id = null, $reply_parameters = null, $reply_markup = null, $protect_content = null, $disable_notification = null, $caption = null, $caption_entities = null): bool|string|array|null
    {
        return $this->endpoint('copyMessage', get_defined_vars());
    }

    public function copyMessages($chat_id, $from_chat_id, $message_ids, $message_thread_id = null, $protect_content = null, $disable_notification = null, $remove_caption = null): bool|string|array|null
    {
        return $this->endpoint('copyMessages', get_defined_vars());
    }

    public function sendPhoto($chat_id, $photo, $caption = null, $parse_mode = null, $message_thread_id = null, $reply_parameters = null, $reply_markup = null, $protect_content = null, $disable_notification = null, $has_spoiler = null, $caption_entities = null, $business_connection_id = null): bool|string|array|null
    {
        return $this->endpoint('sendPhoto', get_defined_vars());
    }

    public function sendAudio($chat_id, $audio, $caption = null, $parse_mode = null, $message_thread_id = null, $duration = null, $performer = null, $title = null, $thumbnail = null, $reply_parameters = null, $reply_markup = null, $protect_content = null, $disable_notification = null, $caption_entities = null, $business_connection_id = null): bool|string|array|null
    {
        return $this->endpoint('sendAudio', get_defined_vars());
    }

    public function sendDocument($chat_id, $document, $caption = null, $parse_mode = null, $message_thread_id = null, $thumbnail = null, $reply_parameters = null, $reply_markup = null, $protect_content = null, $disable_notification = null, $caption_entities = null, $business_connection_id = null, $disable_content_type_detection = null): bool|string|array|null
    {
        return $this->endpoint('sendDocument', get_defined_vars());
    }

    public function sendVideo($chat_id, $video, $caption = null, $parse_mode = null, $message_thread_id = null, $duration = null, $width = null, $height = null, $thumbnail = null, $reply_parameters = null, $reply_markup = null, $protect_content = null, $disable_notification = null, $caption_entities = null, $has_spoiler = null, $supports_streaming = null, $business_connection_id = null): bool|string|array|null
    {
        return $this->endpoint('sendVideo', get_defined_vars());
    }

    public function sendAnimation($chat_id, $animation, $caption = null, $parse_mode = null, $message_thread_id = null, $duration = null, $width = null, $height = null, $thumbnail = null, $reply_parameters = null, $reply_markup = null, $protect_content = null, $disable_notification = null, $caption_entities = null, $has_spoiler = null, $business_connection_id = null): bool|string|array|null
    {
        return $this->endpoint('sendAnimation', get_defined_vars());
    }

    public function sendVoice($chat_id, $voice, $caption = null, $parse_mode = null, $message_thread_id = null, $duration = null, $reply_parameters = null, $reply_markup = null, $protect_content = null, $disable_notification = null, $caption_entities = null, $business_connection_id = null): bool|string|array|null
    {
        return $this->endpoint('sendVoice', get_defined_vars());
    }

    public function sendVideoNote($chat_id, $video_note, $message_thread_id = null, $duration = null, $length = null, $thumbnail = null, $reply_parameters = null, $reply_markup = null, $protect_content = null, $disable_notification = null, $business_connection_id = null): bool|string|array|null
    {
        return $this->endpoint('sendVideoNote', get_defined_vars());
    }

    public function sendMediaGroup($chat_id, $media, $message_thread_id = null, $reply_parameters = null, $protect_content = null, $disable_notification = null, $business_connection_id = null): bool|string|array|null
    {
        return $this->endpoint('sendMediaGroup', get_defined_vars());
    }

    public function sendLocation($chat_id, $latitude, $longitude, $horizontal_accuracy = null, $live_period = null, $heading = null, $proximity_alert_radius = null, $message_thread_id = null, $reply_parameters = null, $reply_markup = null, $protect_content = null, $disable_notification = null, $business_connection_id = null): bool|string|array|null
    {
        return $this->endpoint('sendLocation', get_defined_vars());
    }

    public function sendVenue($chat_id, $latitude, $longitude, $title, $address, $foursquare_id = null, $foursquare_type = null, $google_place_id = null, $google_place_type = null, $message_thread_id = null, $reply_parameters = null, $reply_markup = null, $protect_content = null, $disable_notification = null, $business_connection_id = null): bool|string|array|null
    {
        return $this->endpoint('sendVenue', get_defined_vars());
    }

    public function sendContact($chat_id, $phone_number, $first_name, $last_name = null, $vcard = null, $message_thread_id = null, $reply_parameters = null, $reply_markup = null, $protect_content = null, $disable_notification = null, $business_connection_id = null): bool|string|array|null
    {
        return $this->endpoint('sendContact', get_defined_vars());
    }

    public function sendPoll($chat_id, $question, $options, $is_anonymous = null, $type = null, $allows_multiple_answers = null, $correct_option_id = null, $explanation = null, $explanation_parse_mode = null, $explanation_entities = null, $open_period = null, $close_date = null, $is_closed = null, $message_thread_id = null, $reply_parameters = null, $reply_markup = null, $protect_content = null, $disable_notification = null, $business_connection_id = null): bool|string|array|null
    {
        return $this->endpoint('sendPoll', get_defined_vars());
    }

    public function sendDice($chat_id, $emoji = null, $message_thread_id = null, $reply_parameters = null, $reply_markup = null, $protect_content = null, $disable_notification = null, $business_connection_id = null): bool|string|array|null
    {
        return $this->endpoint('sendDice', get_defined_vars());
    }

    public function sendChatAction($chat_id, $action, $message_thread_id = null, $business_connection_id = null): bool|string|array|null
    {
        return $this->endpoint('sendChatAction', get_defined_vars());
    }

    public function setMessageReaction($chat_id, $message_id, $reaction = null, $is_big = null): bool|string|array|null
    {
        return $this->endpoint('setMessageReaction', get_defined_vars());
    }

    public function getUserProfilePhotos($user_id, $offset = null, $limit = null): bool|string|array|null
    {
        return $this->endpoint('getUserProfilePhotos', get_defined_vars());
    }

    public function getFile($file_id): bool|string|array|null
    {
        return $this->endpoint('getFile', get_defined_vars());
    }

    public function banChatMember($chat_id, $user_id, $until_date = null, $revoke_messages = null): bool|string|array|null
    {
        return $this->endpoint('banChatMember', get_defined_vars());
    }

    public function unbanChatMember($chat_id, $user_id, $only_if_banned = null): bool|string|array|null
    {
        return $this->endpoint('unbanChatMember', get_defined_vars());
    }

    public function restrictChatMember($chat_id, $user_id, $permissions, $until_date = null, $use_independent_chat_permissions = null): bool|string|array|null
    {
        return $this->endpoint('restrictChatMember', get_defined_vars());
    }

    public function promoteChatMember($chat_id, $user_id, $is_anonymous = null, $can_manage_chat = null, $can_delete_messages = null, $can_manage_video_chats = null, $can_restrict_members = null, $can_promote_members = null, $can_change_info = null, $can_invite_users = null, $can_post_stories = null, $can_edit_stories = null, $can_delete_stories = null, $can_post_messages = null, $can_edit_messages = null, $can_pin_messages = null, $can_manage_topics = null): bool|string|array|null
    {
        return $this->endpoint('promoteChatMember', get_defined_vars());
    }

    public function setChatAdministratorCustomTitle($chat_id, $user_id, $title): bool|string|array|null
    {
        return $this->endpoint('setChatAdministratorCustomTitle', get_defined_vars());
    }

    public function banChatSenderChat($chat_id, $sender_chat_id): bool|string|array|null
    {
        return $this->endpoint('banChatSenderChat', get_defined_vars());
    }

    public function unbanChatSenderChat($chat_id, $sender_chat_id): bool|string|array|null
    {
        return $this->endpoint('unbanChatSenderChat', get_defined_vars());
    }

    public function setChatPermissions($chat_id, $permissions, $use_independent_chat_permissions = null): bool|string|array|null
    {
        return $this->endpoint('setChatPermissions', get_defined_vars());
    }

    public function exportChatInviteLink($chat_id): bool|string|array|null
    {
        return $this->endpoint('exportChatInviteLink', get_defined_vars());
    }

    public function createChatInviteLink($chat_id, $name = null, $expire_date = null, $member_limit = null, $creates_join_request = null): bool|string|array|null
    {
        return $this->endpoint('createChatInviteLink', get_defined_vars());
    }

    public function editChatInviteLink($chat_id, $invite_link, $name = null, $expire_date = null, $member_limit = null, $creates_join_request = null): bool|string|array|null
    {
        return $this->endpoint('editChatInviteLink', get_defined_vars());
    }

    public function revokeChatInviteLink($chat_id, $invite_link): bool|string|array|null
    {
        return $this->endpoint('revokeChatInviteLink', get_defined_vars());
    }

    public function approveChatJoinRequest($chat_id, $user_id): bool|string|array|null
    {
        return $this->endpoint('approveChatJoinRequest', get_defined_vars());
    }

    public function declineChatJoinRequest($chat_id, $user_id): bool|string|array|null
    {
        return $this->endpoint('declineChatJoinRequest', get_defined_vars());
    }

    public function setChatPhoto($chat_id, $photo): bool|string|array|null
    {
        return $this->endpoint('setChatPhoto', get_defined_vars());
    }

    public function deleteChatPhoto($chat_id): bool|string|array|null
    {
        return $this->endpoint('deleteChatPhoto', get_defined_vars());
    }

    public function setChatTitle($chat_id, $title): bool|string|array|null
    {
        return $this->endpoint('setChatTitle', get_defined_vars());
    }

    public function setChatDescription($chat_id, $description): bool|string|array|null
    {
        return $this->endpoint('setChatDescription', get_defined_vars());
    }

    public function pinChatMessage($chat_id, $message_id, $disable_notification = null): bool|string|array|null
    {
        return $this->endpoint('pinChatMessage', get_defined_vars());
    }

    public function unpinChatMessage($chat_id, $message_id): bool|string|array|null
    {
        return $this->endpoint('unpinChatMessage', get_defined_vars());
    }

    public function unpinAllChatMessages($chat_id): bool|string|array|null
    {
        return $this->endpoint('unpinAllChatMessages', get_defined_vars());
    }

    public function leaveChat($chat_id): bool|string|array|null
    {
        return $this->endpoint('leaveChat', get_defined_vars());
    }

    public function getChat($chat_id): bool|string|array|null
    {
        return $this->endpoint('getChat', get_defined_vars());
    }

    public function getChatAdministrators($chat_id): bool|string|array|null
    {
        return $this->endpoint('getChatAdministrators', get_defined_vars());
    }

    public function getChatMemberCount($chat_id): bool|string|array|null
    {
        return $this->endpoint('getChatMemberCount', get_defined_vars());
    }

    public function getChatMember($chat_id, $user_id): bool|string|array|null
    {
        return $this->endpoint('getChatMember', get_defined_vars());
    }

    public function setChatStickerSet($chat_id, $sticker_set_name): bool|string|array|null
    {
        return $this->endpoint('setChatStickerSet', get_defined_vars());
    }

    public function deleteChatStickerSet($chat_id): bool|string|array|null
    {
        return $this->endpoint('deleteChatStickerSet', get_defined_vars());
    }

    public function getForumTopicIconStickers(): bool|string|array|null
    {
        return $this->endpoint('getForumTopicIconStickers', get_defined_vars());
    }

    public function createForumTopic($chat_id, $name, $icon_color = null, $icon_custom_emoji_id = null): bool|string|array|null
    {
        return $this->endpoint('createForumTopic', get_defined_vars());
    }

    public function editForumTopic($chat_id, $message_thread_id, $name = null, $icon_custom_emoji_id = null): bool|string|array|null
    {
        return $this->endpoint('editForumTopic', get_defined_vars());
    }

    public function closeForumTopic($chat_id, $message_thread_id): bool|string|array|null
    {
        return $this->endpoint('closeForumTopic', get_defined_vars());
    }

    public function reopenForumTopic($chat_id, $message_thread_id): bool|string|array|null
    {
        return $this->endpoint('reopenForumTopic', get_defined_vars());
    }

    public function deleteForumTopic($chat_id, $message_thread_id): bool|string|array|null
    {
        return $this->endpoint('deleteForumTopic', get_defined_vars());
    }

    public function unpinAllForumTopicMessages($chat_id, $message_thread_id): bool|string|array|null
    {
        return $this->endpoint('unpinAllForumTopicMessages', get_defined_vars());
    }

    public function editGeneralForumTopic($chat_id, $name): bool|string|array|null
    {
        return $this->endpoint('editGeneralForumTopic', get_defined_vars());
    }

    public function closeGeneralForumTopic($chat_id): bool|string|array|null
    {
        return $this->endpoint('closeGeneralForumTopic', get_defined_vars());
    }

    public function reopenGeneralForumTopic($chat_id): bool|string|array|null
    {
        return $this->endpoint('reopenGeneralForumTopic', get_defined_vars());
    }

    public function hideGeneralForumTopic($chat_id): bool|string|array|null
    {
        return $this->endpoint('hideGeneralForumTopic', get_defined_vars());
    }

    public function unhideGeneralForumTopic($chat_id): bool|string|array|null
    {
        return $this->endpoint('unhideGeneralForumTopic', get_defined_vars());
    }

    public function unpinAllGeneralForumTopicMessages($chat_id): bool|string|array|null
    {
        return $this->endpoint('unpinAllGeneralForumTopicMessages', get_defined_vars());
    }

    public function answerCallbackQuery($callback_query_id, $text = null, $show_alert = null, $url = null, $cache_time = null): bool|string|array|null
    {
        return $this->endpoint('answerCallbackQuery', get_defined_vars());
    }

    public function getUserChatBoosts($chat_id, $user_id): bool|string|array|null
    {
        return $this->endpoint('getUserChatBoosts', get_defined_vars());
    }

    public function getBusinessConnection($business_connection_id): bool|string|array|null
    {
        return $this->endpoint('getBusinessConnection', get_defined_vars());
    }

    public function setMyCommands($commands, $scope = null, $language_code = null): bool|string|array|null
    {
        return $this->endpoint('setMyCommands', get_defined_vars());
    }

    public function deleteMyCommands($scope = null, $language_code = null): bool|string|array|null
    {
        return $this->endpoint('deleteMyCommands', get_defined_vars());
    }

    public function getMyCommands($scope = null, $language_code = null): bool|string|array|null
    {
        return $this->endpoint('getMyCommands', get_defined_vars());
    }

    public function setMyName($name = null, $language_code = null): bool|string|array|null
    {
        return $this->endpoint('setMyName', get_defined_vars());
    }

    public function getMyName($language_code = null): bool|string|array|null
    {
        return $this->endpoint('getMyName', get_defined_vars());
    }

    public function setMyDescription($description = null, $language_code = null): bool|string|array|null
    {
        return $this->endpoint('setMyDescription', get_defined_vars());
    }

    public function getMyDescription($language_code = null): bool|string|array|null
    {
        return $this->endpoint('getMyDescription', get_defined_vars());
    }

    public function setMyShortDescription($short_description = null, $language_code = null): bool|string|array|null
    {
        return $this->endpoint('setMyShortDescription', get_defined_vars());
    }

    public function getMyShortDescription($language_code = null): bool|string|array|null
    {
        return $this->endpoint('getMyShortDescription', get_defined_vars());
    }

    public function setChatMenuButton($chat_id = null, $menu_button = null): bool|string|array|null
    {
        return $this->endpoint('setChatMenuButton', get_defined_vars());
    }

    public function getChatMenuButton($chat_id = null): bool|string|array|null
    {
        return $this->endpoint('getChatMenuButton', get_defined_vars());
    }

    public function setMyDefaultAdministratorRights($rights = null, $for_channels = null): bool|string|array|null
    {
        return $this->endpoint('setMyDefaultAdministratorRights', get_defined_vars());
    }

    public function getMyDefaultAdministratorRights($for_channels = null): bool|string|array|null
    {
        return $this->endpoint('getMyDefaultAdministratorRights', get_defined_vars());
    }

    public function editMessageText($text, $chat_id = null, $message_id = null, $inline_message_id = null, $parse_mode = null, $entities = null, $link_preview_options = null, $reply_markup = null): bool|string|array|null
    {
        return $this->endpoint('editMessageText', get_defined_vars());
    }

    public function editMessageCaption($chat_id = null, $message_id = null, $inline_message_id = null, $caption = null, $parse_mode = null, $caption_entities = null, $reply_markup = null): bool|string|array|null
    {
        return $this->endpoint('editMessageCaption', get_defined_vars());
    }

    public function editMessageMedia($media, $chat_id = null, $message_id = null, $inline_message_id = null, $reply_markup = null): bool|string|array|null
    {
        return $this->endpoint('editMessageMedia', get_defined_vars());
    }

    public function editMessageLiveLocation($latitude, $longitude, $chat_id = null, $message_id = null, $inline_message_id = null, $horizontal_accuracy = null, $heading = null, $proximity_alert_radius = null, $reply_markup = null): bool|string|array|null
    {
        return $this->endpoint('editMessageLiveLocation', get_defined_vars());
    }

    public function stopMessageLiveLocation($chat_id = null, $message_id = null, $inline_message_id = null, $reply_markup = null): bool|string|array|null
    {
        return $this->endpoint('stopMessageLiveLocation', get_defined_vars());
    }

    public function editMessageReplyMarkup($chat_id = null, $message_id = null, $inline_message_id = null, $reply_markup = null): bool|string|array|null
    {
        return $this->endpoint('editMessageReplyMarkup', get_defined_vars());
    }

    public function stopPoll($chat_id, $message_id, $reply_markup = null): bool|string|array|null
    {
        return $this->endpoint('stopPoll', get_defined_vars());
    }

    public function deleteMessage($chat_id, $message_id): bool|string|array|null
    {
        return $this->endpoint('deleteMessage', get_defined_vars());
    }

    public function deleteMessages($chat_id, $message_ids): bool|string|array|null
    {
        return $this->endpoint('deleteMessages', get_defined_vars());
    }

    public function sendSticker($chat_id, $sticker, $emoji = null, $message_thread_id = null, $reply_parameters = null, $reply_markup = null, $protect_content = null, $disable_notification = null, $business_connection_id = null): bool|string|array|null
    {
        return $this->endpoint('sendSticker', get_defined_vars());
    }

    public function getStickerSet($name): bool|string|array|null
    {
        return $this->endpoint('getStickerSet', get_defined_vars());
    }

    public function getCustomEmojiStickers($custom_emoji_ids): bool|string|array|null
    {
        return $this->endpoint('getCustomEmojiStickers', get_defined_vars());
    }

    public function uploadStickerFile($user_id, $sticker, $sticker_format): bool|string|array|null
    {
        return $this->endpoint('uploadStickerFile', get_defined_vars());
    }

    public function createNewStickerSet($user_id, $name, $title, $stickers, $sticker_type = null, $needs_repainting = null): bool|string|array|null
    {
        return $this->endpoint('createNewStickerSet', get_defined_vars());
    }

    public function addStickerToSet($user_id, $name, $sticker): bool|string|array|null
    {
        return $this->endpoint('addStickerToSet', get_defined_vars());
    }

    public function setStickerPositionInSet($sticker, $position): bool|string|array|null
    {
        return $this->endpoint('setStickerPositionInSet', get_defined_vars());
    }

    public function deleteStickerFromSet($sticker): bool|string|array|null
    {
        return $this->endpoint('deleteStickerFromSet', get_defined_vars());
    }

    public function replaceStickerInSet($user_id, $name, $old_sticker, $sticker): bool|string|array|null
    {
        return $this->endpoint('replaceStickerInSet', get_defined_vars());
    }

    public function setStickerEmojiList($sticker, $emoji_list): bool|string|array|null
    {
        return $this->endpoint('setStickerEmojiList', get_defined_vars());
    }

    public function setStickerKeywords($sticker, $keywords = null): bool|string|array|null
    {
        return $this->endpoint('setStickerKeywords', get_defined_vars());
    }

    public function setStickerMaskPosition($sticker, $mask_position = null): bool|string|array|null
    {
        return $this->endpoint('setStickerMaskPosition', get_defined_vars());
    }

    public function setStickerSetTitle($name, $title): bool|string|array|null
    {
        return $this->endpoint('setStickerSetTitle', get_defined_vars());
    }

    public function setStickerSetThumbnail($name, $user_id, $format, $thumbnail = null): bool|string|array|null
    {
        return $this->endpoint('setStickerSetThumbnail', get_defined_vars());
    }

    public function setCustomEmojiStickerSetThumbnail($name, $custom_emoji_id = null): bool|string|array|null
    {
        return $this->endpoint('setCustomEmojiStickerSetThumbnail', get_defined_vars());
    }

    public function deleteStickerSet($name): bool|string|array|null
    {
        return $this->endpoint('deleteStickerSet', get_defined_vars());
    }

    public function answerInlineQuery($inline_query_id, $results, $cache_time = null, $is_personal = null, $next_offset = null, $button = null): bool|string|array|null
    {
        return $this->endpoint('answerInlineQuery', get_defined_vars());
    }

    public function answerWebAppQuery($web_app_query_id, $result): bool|string|array|null
    {
        return $this->endpoint('answerWebAppQuery', get_defined_vars());
    }

    public function sendInvoice($chat_id, $title, $description, $payload, $provider_token, $currency, $price, $message_thread_id = null, $max_tip_amount = null, $suggested_tip_amounts = null, $start_parameter = null, $provider_data = null, $photo_url = null, $photo_size = null, $photo_width = null, $photo_height = null, $need_name = null, $need_phone_number = null, $need_email = null, $need_shipping_address = null, $send_phone_number_to_provider = null, $send_email_to_provider = null, $is_flexible = null, $disable_notification = null, $protect_content = null, $reply_parameters = null, $reply_markup = null): bool|string|array|null
    {
        return $this->endpoint('sendInvoice', get_defined_vars());
    }

    public function createInvoiceLink($title, $description, $payload, $provider_token, $currency, $price, $max_tip_amount = null, $suggested_tip_amounts = null, $start_parameter = null, $provider_data = null, $photo_url = null, $photo_size = null, $photo_width = null, $photo_height = null, $need_name = null, $need_phone_number = null, $need_email = null, $need_shipping_address = null, $send_phone_number_to_provider = null, $send_email_to_provider = null, $is_flexible = null): bool|string|array|null
    {
        return $this->endpoint('createInvoiceLink', get_defined_vars());
    }

    public function answerShippingQuery($shipping_query_id, $ok, $shipping_options = null, $error_message = null): bool|string|array|null
    {
        return $this->endpoint('answerShippingQuery', get_defined_vars());
    }

    public function answerPreCheckoutQuery($pre_checkout_query_id, $ok, $error_message = null): bool|string|array|null
    {
        return $this->endpoint('answerPreCheckoutQuery', get_defined_vars());
    }

    public function setPassportDataErrors($user_id, $errors): bool|string|array|null
    {
        return $this->endpoint('setPassportDataErrors', get_defined_vars());
    }

    public function sendGame($chat_id, $game_short_name, $message_thread_id = null, $disable_notification = null, $protect_content = null, $reply_parameters = null, $reply_markup = null, $business_connection_id = null): bool|string|array|null
    {
        return $this->endpoint('sendGame', get_defined_vars());
    }

    public function setGameScore($user_id, $score, $chat_id = null, $message_id = null, $inline_message_id = null, $disable_edit_message = null, $force = null): bool|string|array|null
    {
        return $this->endpoint('setGameScore', get_defined_vars());
    }

    public function getGameHighScores($user_id, $chat_id = null, $message_id = null, $inline_message_id = null): bool|string|array|null
    {
        return $this->endpoint('getGameHighScores', get_defined_vars());
    }
}