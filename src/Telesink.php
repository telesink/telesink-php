<?php

namespace Telesink;

class Telesink
{
    private const VERSION = '1.1.0';

    /**
     * Send an event to Telesink.
     *
     * @param array $params {
     *   @var string $event            Required. Event name.
     *   @var string $text             Required. Short description / title.
     *   @var string|null $emoji       Optional. Unicode emoji.
     *   @var array|null $properties   Optional. Extra data.
     *   @var string|null $occurred_at / $occurredAt  Optional. ISO8601 timestamp (defaults to now).
     *   @var string|null $idempotency_key / $idempotencyKey  Optional. Custom key (defaults to UUIDv4).
     *   @var string|null $endpoint    Optional. Override TELESINK_ENDPOINT env var.
     * }
     * @return bool true on success, false on disabled / missing endpoint / network error
     */
    public static function track(array $params): bool
    {
        $event           = $params['event']           ?? null;
        $text            = $params['text']            ?? null;
        $emoji           = $params['emoji']           ?? null;
        $properties      = $params['properties']      ?? [];
        $occurredAt      = $params['occurred_at'] ?? $params['occurredAt'] ?? null;
        $idempotencyKey  = $params['idempotency_key'] ?? $params['idempotencyKey'] ?? null;
        $endpoint        = $params['endpoint'] ?? null;

        if (!self::isEnabled() || !($endpoint ?? self::getEndpoint())) {
            return false;
        }

        if (!$event || !$text) {
            self::logError('[Telesink] event and text are required');
            return false;
        }

        $payload = [
            'event'           => $event,
            'text'            => $text,
            'emoji'           => $emoji,
            'properties'      => $properties,
            'occurred_at'     => $occurredAt
                ? (new \DateTime($occurredAt))->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d\TH:i:s.vP')
                : (new \DateTime('now', new \DateTimeZone('UTC')))->format('Y-m-d\TH:i:s.vP'),
            'idempotency_key' => $idempotencyKey ?? self::generateUuidV4(),
            'sdk'             => [
                'name'    => 'telesink.php',
                'version' => self::VERSION,
            ],
        ];

        $payload = array_filter($payload, fn($v) => $v !== null);

        return self::post($payload, $payload['idempotency_key'], $endpoint ?? self::getEndpoint());
    }

    private static function post(array $payload, string $idempotencyKey, ?string $endpoint = null): bool
    {
        $ch = curl_init($endpoint ?? self::getEndpoint());

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload, JSON_UNESCAPED_SLASHES));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'User-Agent: telesink.php/' . self::VERSION,
            'Idempotency-Key: ' . $idempotencyKey,
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($error) {
            self::logError("[Telesink] cURL error: $error");
            return false;
        }

        if ($httpCode >= 200 && $httpCode < 300) {
            return true;
        }

        self::logError("[Telesink] HTTP error {$httpCode}");
        return false;
    }

    private static function isEnabled(): bool
    {
        $disabled = $_ENV['TELESINK_DISABLED'] ?? getenv('TELESINK_DISABLED') ?? false;
        return !filter_var($disabled, FILTER_VALIDATE_BOOLEAN);
    }

    private static function getEndpoint(): ?string
    {
        return $_ENV['TELESINK_ENDPOINT'] ?? getenv('TELESINK_ENDPOINT') ?? null;
    }

    private static function logError(string $message): void
    {
        error_log($message);
    }

    private static function generateUuidV4(): string
    {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // version 4
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // variant 1
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
