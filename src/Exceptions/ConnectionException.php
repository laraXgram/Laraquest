<?php

namespace LaraGram\Laraquest\Exceptions;

/**
 * The call never reached Telegram: a network, DNS or TLS failure.
 */
class ConnectionException extends TelegramApiException
{
}
