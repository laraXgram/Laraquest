<?php

namespace LaraGram\Laraquest\Exceptions;

/**
 * A call Telegram refused: the request itself was wrong (error code 400).
 */
class BadRequestException extends TelegramApiException
{
}
