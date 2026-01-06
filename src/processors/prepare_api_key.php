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
 * Snowflake Processor
 *
 * This processor calls the lb_sf_snowflake Action and processes its response.
 *
 * Logic:
 * 1. Call Action (lb_sf_snowflake) with received parameters
 * 2. Parse the response
 * 3. If code != 0, return error; if code == 0, return data.id
 * 4. Store requestId in context
 */

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


// prepare api key
$apiKey = null;
if (RequestChainStorage::has(HttpRequestHeaderConstant::X_API_KEY_LOWER)) {
    $apiKey = RequestChainStorage::get(HttpRequestHeaderConstant::X_API_KEY_LOWER);
}
if (!$apiKey) {
    $apiKey = prepareApiKey($request, $context, $processor, $logger);
}

return $response->build(200, [], [
    'apiKey' => $apiKey,
]);


/**
 * @param RequestInterface $request
 * @param ContextInterface $context
 * @param ProcessorInterface $processor
 * @param LoggerInterface $logger
 * @return string
 */
function prepareApiKey(RequestInterface $request, ContextInterface $context, ProcessorInterface $processor, LoggerInterface $logger): string
{
    $apiKey = 'test-api-key';
    RequestChainStorage::set(HttpRequestHeaderConstant::X_API_KEY_LOWER, $apiKey);
    return $apiKey;
}
