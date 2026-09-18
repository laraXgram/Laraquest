<?php

namespace LaraGram\Laraquest\Exceptions;

/**
 * Telegram failed to handle the call (error code 5xx). Retrying usually helps.
 */
class InternalServerErrorException extends TelegramApiException
{
}
