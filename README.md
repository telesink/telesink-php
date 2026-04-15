# Telesink SDK for PHP

Official PHP client for [telesink.com](https://telesink.com) — real-time event
dashboard.

**Note**: Low activity here doesn’t mean this is abandoned. It’s intentionally
simple, so there’s not much to change.

## Installation

```bash
composer require telesink/telesink
```

## Configuration

```bash
export TELESINK_ENDPOINT=https://app.telesink.com/api/v1/sinks/your_sink_token_here/events
```

To disable tracking (e.g. in tests or development):

```sh
export TELESINK_DISABLED=true
```

## Usage

```php
use Telesink\Telesink;

Telesink::track([
    'event'      => 'User signed up',
    'text'       => 'user@example.com',
    'emoji'      => '👤',                    // optional
    'properties' => [                        // optional
        'user_id' => 123,
        'plan'    => 'pro',
    ],
]);
```

### License

MIT (see [LICENSE.md](/LICENSE.md)).
