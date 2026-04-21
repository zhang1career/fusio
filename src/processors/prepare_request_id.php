<?php
/**
 * @var RequestInterface $request
 * @var ContextInterface $context
 * @var ConnectorInterface $connector
 * @var FactoryInterface $response
 * @var ProcessorInterface $processor
 * @var DispatcherInterface $dispatcher
 * @var LoggerInterface $logger
 * @var CacheInterface $cache
 */

/**
 * Prepares X-Request-Id by calling the Snowflake ID service (POST /api/snowflake/id).
 *
 * Configuration: EXT_SNOWFLAKE_URL, EXT_SNOWFLAKE_ACCESS_TOKEN (JSON field access_key),
 * and optional Redis service-discovery placeholders `://{{service_key}}` (same pattern as EXT_USER_CENTER_URL).
 */

use App\Service\Snowflake\SnowflakeRequestIdClient;
use Fusio\Adapter\Util\Component\RequestChainStorage;
use Fusio\Engine\ConnectorInterface;
use Fusio\Engine\ContextInterface;
use Fusio\Engine\DispatcherInterface;
use Fusio\Engine\ProcessorInterface;
use Fusio\Engine\Request\HttpRequestHeaderConstant;
use Fusio\Engine\RequestInterface;
use Fusio\Engine\Response\FactoryInterface;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;

// prepare requestId
$requestId = null;
if (RequestChainStorage::has(HttpRequestHeaderConstant::X_REQUEST_ID_LOWER)) {
    $requestId = RequestChainStorage::get(HttpRequestHeaderConstant::X_REQUEST_ID_LOWER);
}
if (!$requestId) {
    $requestId = prepareRequestId($logger);
}

return $response->build(200, [], [
    'requestId' => $requestId,
]);


function prepareRequestId(LoggerInterface $logger): string
{
    $requestId = SnowflakeRequestIdClient::fetchRequestId($logger);
    RequestChainStorage::set(HttpRequestHeaderConstant::X_REQUEST_ID_LOWER, $requestId);

    return $requestId;
}
