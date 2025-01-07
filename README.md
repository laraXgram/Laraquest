# Laraquest
Sending requests and receiving Telegram updates.
- Bot API Version `8.2`

## Other Versions
- [Laraquest GoLang](https://github.com/laraXgram/Laraquest-Go)
- Laraquest Python
- Laraquest JavaScript
- Laraquest Rust
- Laraquest C#

## Installation:
```bash
composer require laraxgram/laraquest
```
---
## Config:
```php
$_ENV["BOT_TOKEN"] = "123456798:asdfghjklzxcvbnmqwpoieuryt"; // required
$_ENV["BOT_API_SERVER"] = "https://api.telegram.org/"; // optional

// Polling
$_ENV["sleep_interval"] = 0.5; // optional
$_ENV["timeout"] = 100; // optional
$_ENV["limit"] = 100; // optional
$_ENV["allow_updates"] = ["*"]; // optional
```
---
## Usage:

#### Use Methods:
Just use trait Method in your class!
```php
use LaraGram\Laraquest\Methode;

class MyBotClass {
    use Methode;
}

$bot = new MyBotClass();
$bot->sendMessage(123456789, 'hello!');
```
---
Just use trait Updates in your class!
#### Use Updates:
```php
use LaraGram\Laraquest\Updates;

class MyBotClass {
    use Updates;
}

$bot = new MyBotClass();
$chatID = $bot->message->chat->id;
```
---
#### Use Both:
Just use trait Method and Updates in your class!
```php
use LaraGram\Laraquest\Methode;
use LaraGram\Laraquest\Updates;

class MyBotClass {
    use Methode, Updates;
}

$bot = new MyBotClass();
$bot->sendMessage($bot->message->chat->id, 'hello!');
```


### Long Polling
```php
Laraquest::polling(function(){
    // ...
});

Laraquest::polling(function(Laraquest $request){
    // ...
});
```
