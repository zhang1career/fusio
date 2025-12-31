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
use Fusio\Engine\RequestInterface;
use Fusio\Engine\Response\FactoryInterface;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;


$apiKey = 'test-api-key';

RequestChainStorage::set('X-API-Key', $apiKey);

return $response->build(200, [], [
    'apiKey' => $apiKey,
]);
