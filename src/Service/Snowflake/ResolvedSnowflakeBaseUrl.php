<?php

declare(strict_types=1);

namespace App\Service\Snowflake;

use Fusio\Impl\Exception\InvalidConfigurationException;
use Paganini\Memo\CacheKeyGenerator;
use Paganini\Memo\Memoizer;
use Paganini\ServiceDiscovery\Contracts\ServiceUriResolverInterface;
use Paganini\ServiceDiscovery\ServiceUrlSpecifier;
use PSX\Framework\Config\ConfigInterface;

/**
 * Resolves Snowflake HTTP base URL from {@code ext_snowflake_url} (env {@code EXT_SNOWFLAKE_URL}).
 * When the value contains {@code ://{{service_key}}}, the host segment is resolved via Redis (paganini),
 * same pattern as {@see \Fusio\Impl\Service\System\ResolvedUserCenterBaseUrl}.
 *
 * Memoization TTL uses {@code ext_user_center_sd_memo_ttl_seconds} (env {@code EXT_USER_CENTER_SD_MEMO_TTL}).
 */
final class ResolvedSnowflakeBaseUrl
{
    public function __construct(
        private readonly ConfigInterface $config,
        private readonly Memoizer $memoizer,
        private readonly ServiceUriResolverInterface $serviceUriResolver,
    ) {
    }

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
        if (!str_contains($raw, '://{{')) {
            return rtrim($raw, '/');
        }

        $host = trim((string) $this->config->get('redis_host'));
        if ($host === '') {
            throw new InvalidConfigurationException(
                'ext_snowflake_url contains service-discovery placeholders (`://{{...}}`) but REDIS_HOST (redis_host) is not set.'
            );
        }

        $ttl = (int) $this->config->get('ext_user_center_sd_memo_ttl_seconds');
        if ($ttl < 0) {
            $ttl = 0;
        }

        $cacheKey = 'fusio_app:snowflake_base:' . CacheKeyGenerator::fromAssociativeArray(['u' => $raw]);

        return rtrim(
            (string) $this->memoizer->getOrCompute(
                $cacheKey,
                $ttl,
                fn (): string => ServiceUrlSpecifier::specifyHost($raw, $this->serviceUriResolver)
            ),
            '/'
        );
    }
}
