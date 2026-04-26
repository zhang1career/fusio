<?php

declare(strict_types=1);

namespace App\Service\Snowflake;

use Fusio\Impl\Exception\InvalidConfigurationException;
use Fusio\Impl\Infrastructure\ServiceDiscovery\RedisStringClientFactory;
use Fusio\Impl\Infrastructure\ServiceDiscovery\UserCenterServiceDiscoveryFactories;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerInterface;
use PSX\Framework\Config\Config;
use PSX\Framework\Config\ConfigInterface;
use RuntimeException;

/**
 * Fetches a Snowflake ID via POST {@code /api/snowflake/id} using env configuration.
 */
final class SnowflakeRequestIdClient
{
    private function __construct()
    {
    }

    /**
     * @param string|null $accessKey When not null and not empty after trim, used as access_key for POST /api/snowflake/id;
     *                                    otherwise ext_snowflake_access_key from configuration.
     *
     * @throws RuntimeException
     */
    public static function fetchRequestId(LoggerInterface $logger, ?string $accessKey = null): string
    {
        $config = self::buildConfig();
        try {
            $baseUrl = self::resolveBaseUrl($config);
        } catch (InvalidConfigurationException $e) {
            throw new RuntimeException($e->getMessage(), 0, $e);
        }
        if ($baseUrl === '') {
            throw new RuntimeException(
                'Snowflake base URL is not configured (set EXT_SNOWFLAKE_URL in the environment).'
            );
        }

        $accessKey = $accessKey !== null && trim($accessKey) !== ''
            ? trim($accessKey)
            : trim((string)$config->get('ext_snowflake_access_key'));
        if ($accessKey === '') {
            throw new RuntimeException(
                'Snowflake access key is not configured (set EXT_SNOWFLAKE_ACCESS_KEY in the environment).'
            );
        }

        $url = $baseUrl . '/api/snowflake/id';
        $client = new Client(['timeout' => 15]);

        try {
            $httpResponse = $client->post($url, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'access_key' => $accessKey,
                ],
            ]);
        } catch (GuzzleException $e) {
            throw new RuntimeException('Snowflake HTTP request failed: ' . $e->getMessage(), 0, $e);
        }

        $body = (string)$httpResponse->getBody();
        $result = json_decode($body, true);
        if (!is_array($result)) {
            throw new RuntimeException('Snowflake response is not valid JSON.');
        }

        if (!array_key_exists('errorCode', $result)) {
            throw new RuntimeException('Snowflake response is missing errorCode.');
        }
        if ((int)$result['errorCode'] !== 0) {
            $msg = isset($result['message']) ? (string)$result['message'] : 'Unknown error';
            throw new RuntimeException('Snowflake service error: ' . $msg);
        }
        $data = $result['data'] ?? null;
        if (!is_array($data) || !isset($data['id'])) {
            throw new RuntimeException('Snowflake response data is missing id field.');
        }

        $requestId = (string)$data['id'];
        $logger->info('Request ID: ' . $requestId);

        return $requestId;
    }

    /**
     * @throws InvalidConfigurationException
     */
    private static function resolveBaseUrl(ConfigInterface $config): string
    {
        $redisFactory = new RedisStringClientFactory();
        $resolver = UserCenterServiceDiscoveryFactories::createRedisServiceUriResolver($config, $redisFactory);
        $resolved = new ResolvedSnowflakeBaseUrl($config, $resolver);

        return $resolved->resolve();
    }

    private static function buildConfig(): ConfigInterface
    {
        return new Config([
            'ext_snowflake_url' => self::envString('EXT_SNOWFLAKE_URL'),
            'ext_snowflake_access_key' => self::envString('EXT_SNOWFLAKE_ACCESS_KEY'),
            'redis_host' => self::envString('REDIS_HOST'),
            'redis_scheme' => self::envString('REDIS_SCHEME') ?: 'tcp',
            'redis_port' => self::intEnv('REDIS_PORT', 6379),
            'redis_prefix_register_service' => self::envString('REDIS_PREFIX_REGISTER_SERVICE'),
            'ext_user_center_sd_memo_ttl_seconds' => max(0, self::intEnv('EXT_USER_CENTER_SD_MEMO_TTL', 60)),
        ]);
    }

    private static function envString(string $key): string
    {
        $v = $_ENV[$key] ?? getenv($key);
        if ($v === false || $v === null) {
            return '';
        }

        return trim((string)$v);
    }

    private static function intEnv(string $key, int $default): int
    {
        $raw = self::envString($key);
        if ($raw === '') {
            return $default;
        }

        return (int)$raw;
    }
}
