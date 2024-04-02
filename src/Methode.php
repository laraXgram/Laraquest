<?php

namespace LaraGram\Laraquest;

use LaraGram\Laraquest\Connection\AMP;
use LaraGram\Laraquest\Connection\Curl;
use LaraGram\Laraquest\Connection\NoResponseCurl;
use Exception;

/**
 * show off @method
 *
 * @method getMe() getMe()
 * @method logOut() logOut()
 * @method close() close()
 * @method sendMessage() sendMessage($chat_id, $text, $parse_mode = null, $message_thread_id = null, $reply_parameters = null, $reply_markup = null, $protect_content = null, $disable_notification = null, $link_preview_options = null, $entities = null, $business_connection_id = null)
 * @method forwardMessage() forwardMessage($chat_id, $from_chat_id, $message_id, $message_thread_id = null, $protect_content = null, $disable_notification = null)
 * @method forwardMessages() forwardMessages($chat_id, $from_chat_id, $message_ids, $message_thread_id = null, $protect_content = null, $disable_notification = null)
 * @method copyMessage() copyMessage($chat_id, $from_chat_id, $message_id, $parse_mode = null, $message_thread_id = null, $reply_parameters = null, $reply_markup = null, $protect_content = null, $disable_notification = null, $caption = null, $caption_entities = null)
 * @method copyMessages() copyMessages($chat_id, $from_chat_id, $message_ids, $message_thread_id = null, $protect_content = null, $disable_notification = null, $remove_caption = null)
 * @method sendPhoto() sendPhoto($chat_id, $photo, $caption = null, $parse_mode = null, $message_thread_id = null, $reply_parameters = null, $reply_markup = null, $protect_content = null, $disable_notification = null, $has_spoiler = null, $caption_entities = null, $business_connection_id = null)
 * @method sendAudio() sendAudio($chat_id, $audio, $caption = null, $parse_mode = null, $message_thread_id = null, $duration = null, $performer = null, $title = null, $thumbnail = null, $reply_parameters = null, $reply_markup = null, $protect_content = null, $disable_notification = null, $caption_entities = null, $business_connection_id = null)
 * @method sendDocument() sendDocument($chat_id, $document, $caption = null, $parse_mode = null, $message_thread_id = null, $thumbnail = null, $reply_parameters = null, $reply_markup = null, $protect_content = null, $disable_notification = null, $caption_entities = null, $business_connection_id = null, $disable_content_type_detection = null)
 * @method sendVideo() sendVideo($chat_id, $video, $caption = null, $parse_mode = null, $message_thread_id = null, $duration = null, $width = null, $height = null, $thumbnail = null, $reply_parameters = null, $reply_markup = null, $protect_content = null, $disable_notification = null, $caption_entities = null, $has_spoiler = null, $supports_streaming = null, $business_connection_id = null)
 * @method sendAnimation() sendAnimation($chat_id, $animation, $caption = null, $parse_mode = null, $message_thread_id = null, $duration = null, $width = null, $height = null, $thumbnail = null, $reply_parameters = null, $reply_markup = null, $protect_content = null, $disable_notification = null, $caption_entities = null, $has_spoiler = null, $business_connection_id = null)
 * @method sendVoice() sendVoice($chat_id, $voice, $caption = null, $parse_mode = null, $message_thread_id = null, $duration = null, $reply_parameters = null, $reply_markup = null, $protect_content = null, $disable_notification = null, $caption_entities = null, $business_connection_id = null)
 * @method sendVideoNote() sendVideoNote($chat_id, $video_note, $message_thread_id = null, $duration = null, $length = null, $thumbnail = null, $reply_parameters = null, $reply_markup = null, $protect_content = null, $disable_notification = null, $business_connection_id = null)
 * @method sendMediaGroup() sendMediaGroup($chat_id, $media, $message_thread_id = null, $reply_parameters = null, $protect_content = null, $disable_notification = null, $business_connection_id = null)
 * @method sendLocation() sendLocation($chat_id, $latitude, $longitude, $horizontal_accuracy = null, $live_period = null, $heading = null, $proximity_alert_radius = null, $message_thread_id = null, $reply_parameters = null, $reply_markup = null, $protect_content = null, $disable_notification = null, $business_connection_id = null)
 * @method sendVenue() sendVenue($chat_id, $latitude, $longitude, $title, $address, $foursquare_id = null, $foursquare_type = null, $google_place_id = null, $google_place_type = null, $message_thread_id = null, $reply_parameters = null, $reply_markup = null, $protect_content = null, $disable_notification = null, $business_connection_id = null)
 * @method sendContact() sendContact($chat_id, $phone_number, $first_name, $last_name = null, $vcard = null, $message_thread_id = null, $reply_parameters = null, $reply_markup = null, $protect_content = null, $disable_notification = null, $business_connection_id = null)
 * @method sendPoll() sendPoll($chat_id, $question, $options, $is_anonymous = null, $type = null, $allows_multiple_answers = null, $correct_option_id = null, $explanation = null, $explanation_parse_mode = null, $explanation_entities = null, $open_period = null, $close_date = null, $is_closed = null, $message_thread_id = null, $reply_parameters = null, $reply_markup = null, $protect_content = null, $disable_notification = null, $business_connection_id = null)
 * @method sendDice() sendDice($chat_id, $emoji = null, $message_thread_id = null, $reply_parameters = null, $reply_markup = null, $protect_content = null, $disable_notification = null, $business_connection_id = null)
 * @method sendChatAction() sendChatAction($chat_id, $action, $message_thread_id = null, $business_connection_id = null)
 * @method setMessageReaction() setMessageReaction($chat_id, $message_id, $reaction = null, $is_big = null)
 * @method getUserProfilePhotos() getUserProfilePhotos($user_id, $offset = null, $limit = null)
 * @method getFile() getFile($file_id)
 * @method banChatMember() banChatMember($chat_id, $user_id, $until_date = null, $revoke_messages = null)
 * @method unbanChatMember() unbanChatMember($chat_id, $user_id, $only_if_banned = null)
 * @method restrictChatMember() restrictChatMember($chat_id, $user_id, $permissions, $until_date = null, $use_independent_chat_permissions = null)
 * @method promoteChatMember() promoteChatMember($chat_id, $user_id, $is_anonymous = null, $can_manage_chat = null, $can_delete_messages = null, $can_manage_video_chats = null, $can_restrict_members = null, $can_promote_members = null, $can_change_info = null, $can_invite_users = null, $can_post_stories = null, $can_edit_stories = null, $can_delete_stories = null, $can_post_messages = null, $can_edit_messages = null, $can_pin_messages = null, $can_manage_topics = null)
 * @method setChatAdministratorCustomTitle() setChatAdministratorCustomTitle($chat_id, $user_id, $title)
 * @method banChatSenderChat() banChatSenderChat($chat_id, $sender_chat_id)
 * @method unbanChatSenderChat() unbanChatSenderChat($chat_id, $sender_chat_id)
 * @method setChatPermissions() setChatPermissions($chat_id, $permissions, $use_independent_chat_permissions = null)
 * @method exportChatInviteLink() exportChatInviteLink($chat_id)
 * @method createChatInviteLink() createChatInviteLink($chat_id, $name = null, $expire_date = null, $member_limit = null, $creates_join_request = null)
 * @method editChatInviteLink() editChatInviteLink($chat_id, $invite_link, $name = null, $expire_date = null, $member_limit = null, $creates_join_request = null)
 * @method revokeChatInviteLink() revokeChatInviteLink($chat_id, $invite_link)
 * @method approveChatJoinRequest() approveChatJoinRequest($chat_id, $user_id)
 * @method declineChatJoinRequest() declineChatJoinRequest($chat_id, $user_id)
 * @method setChatPhoto() setChatPhoto($chat_id, $photo)
 * @method deleteChatPhoto() deleteChatPhoto($chat_id)
 * @method setChatTitle() setChatTitle($chat_id, $title)
 * @method setChatDescription() setChatDescription($chat_id, $description)
 * @method pinChatMessage() pinChatMessage($chat_id, $message_id, $disable_notification = null)
 * @method unpinChatMessage() unpinChatMessage($chat_id, $message_id)
 * @method unpinAllChatMessages() unpinAllChatMessages($chat_id)
 * @method leaveChat() leaveChat($chat_id)
 * @method getChat() getChat($chat_id)
 * @method getChatAdministrators() getChatAdministrators($chat_id)
 * @method getChatMemberCount() getChatMemberCount($chat_id)
 * @method getChatMember() getChatMember($chat_id, $user_id)
 * @method setChatStickerSet() setChatStickerSet($chat_id, $sticker_set_name)
 * @method deleteChatStickerSet() deleteChatStickerSet($chat_id)
 * @method getForumTopicIconStickers() getForumTopicIconStickers()
 * @method createForumTopic() createForumTopic($chat_id, $name, $icon_color = null, $icon_custom_emoji_id = null)
 * @method editForumTopic() editForumTopic($chat_id, $message_thread_id, $name = null, $icon_custom_emoji_id = null)
 * @method closeForumTopic() closeForumTopic($chat_id, $message_thread_id)
 * @method reopenForumTopic() reopenForumTopic($chat_id, $message_thread_id)
 * @method deleteForumTopic() deleteForumTopic($chat_id, $message_thread_id)
 * @method unpinAllForumTopicMessages() unpinAllForumTopicMessages($chat_id, $message_thread_id)
 * @method editGeneralForumTopic() editGeneralForumTopic($chat_id, $name)
 * @method closeGeneralForumTopic() closeGeneralForumTopic($chat_id)
 * @method reopenGeneralForumTopic() reopenGeneralForumTopic($chat_id)
 * @method hideGeneralForumTopic() hideGeneralForumTopic($chat_id)
 * @method unhideGeneralForumTopic() unhideGeneralForumTopic($chat_id)
 * @method unpinAllGeneralForumTopicMessages() unpinAllGeneralForumTopicMessages($chat_id)
 * @method answerCallbackQuery() answerCallbackQuery($callback_query_id, $text = null, $show_alert = null, $url = null, $cache_time = null)
 * @method getUserChatBoosts() getUserChatBoosts($chat_id, $user_id)
 * @method getBusinessConnection() getBusinessConnection($business_connection_id)
 * @method setMyCommands() setMyCommands($commands, $scope = null, $language_code = null)
 * @method deleteMyCommands() deleteMyCommands($scope = null, $language_code = null)
 * @method getMyCommands() getMyCommands($scope = null, $language_code = null)
 * @method setMyName() setMyName($name = null, $language_code = null)
 * @method getMyName() getMyName($language_code = null)
 * @method setMyDescription() setMyDescription($description = null, $language_code = null)
 * @method getMyDescription() getMyDescription($language_code = null)
 * @method setMyShortDescription() setMyShortDescription($short_description = null, $language_code = null)
 * @method getMyShortDescription() getMyShortDescription($language_code = null)
 * @method setChatMenuButton() setChatMenuButton($chat_id = null, $menu_button = null)
 * @method getChatMenuButton() getChatMenuButton($chat_id = null)
 * @method setMyDefaultAdministratorRights() setMyDefaultAdministratorRights($rights = null, $for_channels = null)
 * @method getMyDefaultAdministratorRights() getMyDefaultAdministratorRights($for_channels = null)
 * @method editMessageText() editMessageText($text, $chat_id = null, $message_id = null, $inline_message_id = null, $parse_mode = null, $entities = null, $link_preview_options = null, $reply_markup = null)
 * @method editMessageCaption() editMessageCaption($chat_id = null, $message_id = null, $inline_message_id = null, $caption = null, $parse_mode = null, $caption_entities = null, $reply_markup = null)
 * @method editMessageMedia() editMessageMedia($media, $chat_id = null, $message_id = null, $inline_message_id = null, $reply_markup = null)
 * @method editMessageLiveLocation() editMessageLiveLocation($latitude, $longitude, $chat_id = null, $message_id = null, $inline_message_id = null, $horizontal_accuracy = null, $heading = null, $proximity_alert_radius = null, $reply_markup = null)
 * @method stopMessageLiveLocation() stopMessageLiveLocation($chat_id = null, $message_id = null, $inline_message_id = null, $reply_markup = null)
 * @method editMessageReplyMarkup() editMessageReplyMarkup($chat_id = null, $message_id = null, $inline_message_id = null, $reply_markup = null)
 * @method stopPoll() stopPoll($chat_id, $message_id, $reply_markup = null)
 * @method deleteMessage() deleteMessage($chat_id, $message_id)
 * @method deleteMessages() deleteMessages($chat_id, $message_ids)
 * @method sendSticker() sendSticker($chat_id, $sticker, $emoji = null, $message_thread_id = null, $reply_parameters = null, $reply_markup = null, $protect_content = null, $disable_notification = null, $business_connection_id = null)
 * @method getStickerSet() getStickerSet($name)
 * @method getCustomEmojiStickers() getCustomEmojiStickers($custom_emoji_ids)
 * @method uploadStickerFile() uploadStickerFile($user_id, $sticker, $sticker_format)
 * @method createNewStickerSet() createNewStickerSet($user_id, $name, $title, $stickers, $sticker_type = null, $needs_repainting = null)
 * @method addStickerToSet() addStickerToSet($user_id, $name, $sticker)
 * @method setStickerPositionInSet() setStickerPositionInSet($sticker, $position)
 * @method deleteStickerFromSet() deleteStickerFromSet($sticker)
 * @method replaceStickerInSet() replaceStickerInSet($user_id, $name, $old_sticker, $sticker)
 * @method setStickerEmojiList() setStickerEmojiList($sticker, $emoji_list)
 * @method setStickerKeywords() setStickerKeywords($sticker, $keywords = null)
 * @method setStickerMaskPosition() setStickerMaskPosition($sticker, $mask_position = null)
 * @method setStickerSetTitle() setStickerSetTitle($name, $title)
 * @method setStickerSetThumbnail() setStickerSetThumbnail($name, $user_id, $format, $thumbnail = null)
 * @method setCustomEmojiStickerSetThumbnail() setCustomEmojiStickerSetThumbnail($name, $custom_emoji_id = null)
 * @method deleteStickerSet() deleteStickerSet($name)
 * @method answerInlineQuery() answerInlineQuery($inline_query_id, $results, $cache_time = null, $is_personal = null, $next_offset = null, $button = null)
 * @method answerWebAppQuery() answerWebAppQuery($web_app_query_id, $result)
 * @method sendInvoice() sendInvoice($chat_id, $title, $description, $payload, $provider_token, $currency, $price, $message_thread_id = null, $max_tip_amount = null, $suggested_tip_amounts = null, $start_parameter = null, $provider_data = null, $photo_url = null, $photo_size = null, $photo_width = null, $photo_height = null, $need_name = null, $need_phone_number = null, $need_email = null, $need_shipping_address = null, $send_phone_number_to_provider = null, $send_email_to_provider = null, $is_flexible = null, $disable_notification = null, $protect_content = null, $reply_parameters = null, $reply_markup = null)
 * @method createInvoiceLink() createInvoiceLink($title, $description, $payload, $provider_token, $currency, $price, $max_tip_amount = null, $suggested_tip_amounts = null, $start_parameter = null, $provider_data = null, $photo_url = null, $photo_size = null, $photo_width = null, $photo_height = null, $need_name = null, $need_phone_number = null, $need_email = null, $need_shipping_address = null, $send_phone_number_to_provider = null, $send_email_to_provider = null, $is_flexible = null)
 * @method answerShippingQuery() answerShippingQuery($shipping_query_id, $ok, $shipping_options = null, $error_message = null)
 * @method answerPreCheckoutQuery() answerPreCheckoutQuery($pre_checkout_query_id, $ok, $error_message = null)
 * @method setPassportDataErrors() setPassportDataErrors($user_id, $errors)
 * @method sendGame() sendGame($chat_id, $game_short_name, $message_thread_id = null, $disable_notification = null, $protect_content = null, $reply_parameters = null, $reply_markup = null, $business_connection_id = null)
 * @method setGameScore() setGameScore($user_id, $score, $chat_id = null, $message_id = null, $inline_message_id = null, $disable_edit_message = null, $force = null)
 * @method getGameHighScores() getGameHighScores($user_id, $chat_id = null, $message_id = null, $inline_message_id = null)
 */
class Methode
{
    private $mode = 32;

    public function mode(Mode $mode): static
    {
        $this->mode = $mode->value;
        return $this;
    }

    function __call($method, $params)
    {
        if ($this->mode == 32) {
            return (new Curl($_ENV['BOT_TOKEN'], $_ENV['API_SERVER']))->endpoint($method, [...$params]);
        } elseif ($this->mode = 64) {
            return (new NoResponseCurl($_ENV['BOT_TOKEN'], $_ENV['API_SERVER']))->endpoint($method, [...$params]);
        } elseif ($this->mode = 128) {
            return (new AMP($_ENV['BOT_TOKEN'], $_ENV['API_SERVER']))->endpoint($method, [...$params]);
        } elseif ($this->mode = 256) {
            throw new Exception('mode OPENSWOOLE not exist yet!');
        }
    }
}