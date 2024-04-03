<?php

namespace LaraGram\Laraquest\Connection;

class NoResponseCurl
{

    private string $url;
    private bool $post;
    private array|null $content;
    private mixed $handle;
    private string $command;

    public function __construct(
        public string $token,
        public string $api_server = "https://api.telegram.org/"
    ) { }

    private function set_url(string $methode): void
    {
        if (str_ends_with($this->api_server, '/' )){
            $this->url = $this->api_server . 'bot' . $this->token . "/" . $methode;
        }else{
            $this->url = $this->api_server . '/bot' . $this->token . "/" . $methode;
        }
    }

    public function set_option()
    {
        $http_methode = 'POST' ? $this->post : $http_methode = 'GET';
        $this->command = "curl --parallel --parallel-immediate --parallel-max 100 --tcp-fastopen --tcp-nodelay -X {$http_methode} -H 'Content-type: application/json' -d " . escapeshellarg(json_encode($this->content)) . " '{$this->url}' -o /dev/null >> /dev/null 2>&1 &";
    }

    private function execute(): bool|string
    {
        $this->handle = popen($this->command, 'r');
        return stream_get_contents($this->handle);
    }

    private function close(): void
    {
        pclose($this->handle);
    }

    public function endpoint(string $methode, array $content, bool $post = true): bool|string
    {
        $this->set_url($methode);
        $this->content = $content;
        $this->post = $post;
        $this->set_option();
        $result = $this->execute();
        $this->close();

        return $result;
    }
}