<?php

declare(strict_types=1);

namespace App\Service\Snowflake;

use Fusio\Impl\Exception\InvalidConfigurationException;
use Paganini\ServiceDiscovery\Contracts\ServiceUriResolverInterface;
use Paganini\ServiceDiscovery\ServiceUrlSpecifier;
use PSX\Framework\Config\ConfigInterface;

/**
 * Resolves Snowflake HTTP base URL from {@code ext_snowflake_url} (env {@code EXT_SNOWFLAKE_URL}).
 * When the value contains {@code ://{{service_key}}}, the host segment is resolved via Redis (paganini),
 * same pattern as {@see \Fusio\Impl\Service\System\ResolvedUserCenterBaseUrl}.
 *
 * The resolved service instance URL is not memoized: caching would pin one backend and break load balancing
 * when multiple nodes register under the same key.
 */
final readonly class ResolvedSnowflakeBaseUrl
{
    public function __construct(
        private ConfigInterface $config,
        private ServiceUriResolverInterface $serviceUriResolver,
    ) {}

    /**
     * Trimmed Snowflake API base URL, or empty string if unset.
     *
     * @throws InvalidConfigurationException
     */
    public function resolve(): string
    {
        $raw = (string) $this->config->get('ext_snowflake_url');
        if ($raw === '') {
            return '';
        }
        if (! str_contains($raw, '://{{')) {
            return rtrim($raw, '/');
        }

        $host = trim((string) $this->config->get('redis_host'));
        if ($host === '') {
            throw new InvalidConfigurationException(
                'ext_snowflake_url contains service-discovery placeholders (`://{{...}}`) but REDIS_HOST (redis_host) is not set.'
            );
        }

        return rtrim(
            ServiceUrlSpecifier::specifyHost($raw, $this->serviceUriResolver),
            '/'
        );
    }
}
