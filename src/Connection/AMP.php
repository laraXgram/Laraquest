<?php

namespace LaraGram\Laraquest\Connection;

use Amp\Loop;
use Bot\Core\Connect\Amphp;

class AMP
{
    private string $url;
    private bool $post;
    private array|null $content;

    public function __construct(
        public string $token,
        public string $api_server = "https://api.telegram.org/"
    ) { }

    private function set_url(string $methode): void
    {
        $this->url = $this->api_server . 'bot' . $this->token . "/" . $methode;
    }

    private function execute()
    {
        Loop::run(function () {
            $apiClient = new Amphp($this->url);

            if ($this->post) {
                $response = yield $apiClient->post($this->content);
            } else {
                $response = yield $apiClient->get();
            }

            return yield $response->getBody()->buffer();
        });
        return false;
    }

    public function endpoint(string $methode, array $content, bool $post = true)
    {
        $this->set_url($methode);
        $this->content = $content;
        $this->post = $post;
        return $this->execute();
    }
}
