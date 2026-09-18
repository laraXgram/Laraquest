<?php

namespace LaraGram\Laraquest\Exceptions;

/**
 * An edit that would leave the message exactly as it is.
 */
class MessageNotModifiedException extends BadRequestException
{
}
