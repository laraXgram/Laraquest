<?php

namespace LaraGram\Laraquest\Exceptions;

/**
 * The bot is sending too fast (error code 429). Wait retryAfter() seconds.
 */
class FloodException extends TelegramApiException
{
}
