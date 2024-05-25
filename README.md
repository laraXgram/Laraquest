# Laraquest

## Installation:
```bash
composer require laraxgram/laraquest
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
