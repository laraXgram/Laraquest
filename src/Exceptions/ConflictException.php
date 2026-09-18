<?php

namespace LaraGram\Laraquest\Exceptions;

/**
 * The bot is busy elsewhere, such as a webhook competing with getUpdates (error code 409).
 */
class ConflictException extends TelegramApiException
{
}
