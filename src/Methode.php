<?php

namespace LaraGram\Laraquest;

use LaraGram\Laraquest\Connection\Curl;
use LaraGram\Laraquest\Connection\NoResponseCurl;

trait Methode
{
    use APIMethods;

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
}

