<?php

namespace LaraGram\Laraquest\Connection;

use CurlHandle;

class Curl
{
    private CurlHandle $curl;
    private string $url;
    private bool $post;
    private array|null $content;
    public function __construct(
        public string $token,
        public string $api_server = "https://api.telegram.org/"
    ) { }

    private function init(): void
    {
        $this->curl = curl_init();
    }

    private function set_url(string $methode): void
    {
        if (str_ends_with($this->api_server, '/' )){
            $this->url = $this->api_server . 'bot' . $this->token . "/" . $methode;
        }else{
            $this->url = $this->api_server . '/bot' . $this->token . "/" . $methode;
        }
    }

    private function set_option(): void
    {
        curl_setopt($this->curl, CURLOPT_URL, $this->url);
        curl_setopt($this->curl, CURLOPT_HEADER, false);
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->curl, CURLOPT_TCP_FASTOPEN, true);
        curl_setopt($this->curl, CURLOPT_TCP_NODELAY, true);

        if ($this->post) {
            curl_setopt($this->curl, CURLOPT_POST, 1);
            curl_setopt($this->curl, CURLOPT_POSTFIELDS, $this->content);
        }
    }

    private function execute(): bool|string
    {
        return curl_exec($this->curl);
    }

    private function close(): void
    {
        curl_close($this->curl);
    }

    public function endpoint(string $methode, array $content, bool $post = true): array
    {
        $this->init();
        $this->set_url($methode);
        $this->content = $content;
        $this->post = $post;
        $this->set_option();
        $result = $this->execute();

        if ($result === false) {
            $errno = curl_errno($this->curl);
            $error = curl_error($this->curl);

            $result = json_encode([
                'ok' => false,
                'error_code' => $errno,
                'description' => $error,
                'code' => $errno,
                'message' => $error,
                'network' => true,
            ]);
        }

        $this->close();

        $decoded = json_decode($result, true);

        // A response that is not JSON at all (a proxy error page, a truncated
        // body) still has to reach the caller in the shape of a failure.
        if (! is_array($decoded)) {
            return [
                'ok' => false,
                'error_code' => 0,
                'description' => 'Malformed response from the Bot API server.',
                'network' => true,
                'body' => is_string($result) ? $result : null,
            ];
        }

        return $decoded;
    }
}

