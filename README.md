# Laraquest

## Installation:
```bash
composer require laraxgram/laraquest
```
---
## Config:
```php
$_ENV["update_type"] = 'sync';
$_ENV["BOT_TOKEN"] = "123456798:asdfghjklzxcvbnmqwpoieuryt";
$_ENV["BOT_API_SERVER"] = "https://api.telegram.org/";

// Polling
$_ENV["sleep_interval"] = 0.5;
$_ENV["timeout"] = 100;
$_ENV["limit"] = 100;
$_ENV["allow_updates"] = ["*"];
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
