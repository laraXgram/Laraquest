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

### Responses

Every method gives back a `Response`, which may be read as the array Telegram
sent, as the object the method returns, or through its own helpers - and the
method documents all three, so an editor completes whichever you use:

```php
$response = $bot->sendMessage(123456789, 'hello!');

$response['result']['message_id'];  // the array Telegram sent
$response->message_id;              // the result's fields, as properties
$response->result();                // the Message object

$bot->getMe()->first_name;          // "LaraGram"
```

```php
$response->isOk();            // bool
$response->failed();          // bool
$response->errorCode();       // int|null   (error_code() works too)
$response->description();     // string|null
$response->retryAfter();      // int|null
$response->migrateToChatId(); // int|null
$response->toArray();         // the result as an array
$response->toArray(true);     // the whole response: ok, result, description...
$response->toJson();          // the result as JSON  (toJson(true) for all of it)
$response->throw();           // raise the matching exception when it failed
```

### Errors

A call Telegram refuses returns `['ok' => false, ...]`. Call `throw()` to get
the exception that matches the failure instead:

```php
use LaraGram\Laraquest\Exceptions\BotBlockedException;
use LaraGram\Laraquest\Exceptions\FloodException;
use LaraGram\Laraquest\Exceptions\TelegramApiException;

try {
    $bot->throw()->sendMessage($chatId, 'hello!');
} catch (BotBlockedException) {
    // the user blocked the bot
} catch (FloodException $e) {
    sleep($e->retryAfter());
} catch (TelegramApiException $e) {
    report($e->method(), $e->errorCode(), $e->description());
}
```

`Laraquest::throwOnErrors()` turns it on for every call, and `silent()` opts a
single call back out. The exceptions are `BadRequestException`,
`UnauthorizedException` (`InvalidTokenException`), `ForbiddenException`
(`BotBlockedException`, `BotKickedException`, `UserDeactivatedException`,
`NotEnoughRightsException`), `NotFoundException`, `ConflictException`,
`RequestEntityTooLargeException`, `FloodException`,
`InternalServerErrorException`, `ConnectionException`, and the ones for the
everyday mistakes: `ChatNotFoundException`, `UserNotFoundException`,
`MessageNotFoundException`, `MessageNotModifiedException`,
`ChatMigratedException` (`migrateToChatId()`) and `InvalidFileException`.

### Update Objects

Every Bot API type has a class with one `init()` parameter per field, so a
payload can be built without remembering a single key:

```php
use LaraGram\Laraquest\Updates\InlineKeyboardButton;
use LaraGram\Laraquest\Updates\InlineKeyboardMarkup;

$bot->sendMessage(123456789, 'Pick one', reply_markup: InlineKeyboardMarkup::init(
    inline_keyboard: [[
        InlineKeyboardButton::init(text: 'Open', url: 'https://t.me/laraxgram'),
    ]],
));
```

The same classes read what Telegram sent, turning nested payloads into objects:

```php
use LaraGram\Laraquest\Updates\Update;

$update = Update::from($bot->getData());

$update->message->chat->id;             // objects all the way down
$update->message->entities[0]->type;    // lists too
$update->get('message.from.username');  // or a dotted path
$update->has('message.photo');
$update->message->only(['message_id', 'text'])->toArray();
```

`$bot->update()` returns the same object for the incoming update.

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
