# Laraquest
Sending requests and receiving Telegram updates.

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
$_ENV["BOT_TOKEN"] = "123456789:ABcdEfGHigKLMnopQrStuvWXYZ"; // required
$_ENV["BOT_API_SERVER"] = "https://api.telegram.org/"; // default-optional

// Polling
$_ENV["sleep_interval"] = 0.5; // default-optional
$_ENV["timeout"] = 100; // default-optional
$_ENV["limit"] = 100; // default-optional
$_ENV["allow_updates"] = ["*"]; // default-optional
```
---
## Usage:

#### Methods:
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
#### Updates:

```php
use LaraGram\Laraquest\Updates;

class MyBotClass {
    use Updates;
}

$bot = new MyBotClass();
$chatID = $bot->message->chat->id;
```
---
#### Both:
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
Laraquest::polling(function(Laraquest $request){
    $request->sendMessage($request->message->chat->id, "Hello, Laraquest!")
});
```

### Multi Connection
```php
$_ENV['CONNECTIONS']['first_bot']['BOT_TOKEN'] = 'XXX';
$_ENV['CONNECTIONS']['second_bot']['BOT_TOKEN'] = 'YYY';

$first_bot = $bot->connection('first_bot');
$second_bot = $bot->connection('second_bot');

$first_bot->sendMessage(...);
$second_bot->deleteMessage(...);

$first_bot->getConnection(); // first_bot
```
