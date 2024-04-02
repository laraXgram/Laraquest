<?php

namespace LaraGram\Laraquest;

enum Mode: int
{
    case CURL = 32;
    case NO_RESPONSE_CURL = 64;
    case AMP = 128;
    case OPENSWOOLE = 256;
}