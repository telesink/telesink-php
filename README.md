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
export TELESINK_ENDPOINT="https://app.telesink.com/api/v1/sinks/your_sink_token_here/events"
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

**Optional: Sending to a different sink**

You can send events to a different sink by passing an `endpoint` key (falls back
to `TELESINK_ENDPOINT` if not set):

```php
Telesink::track([
    'event'      => 'Job succeeded',
    'text'       => 'ProcessUserData',
    'emoji'      => '✅',
    'properties' => ['duration_ms' => 420],
    'endpoint'   => $_ENV['TELESINK_TEST_ENDPOINT'] ?? null,
]);
```

### License

MIT (see [LICENSE.md](/LICENSE.md)).
