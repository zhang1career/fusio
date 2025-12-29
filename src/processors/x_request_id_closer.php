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
use Fusio\Engine\Exception\ActionNotFoundException;
use Fusio\Engine\Exception\FactoryResolveException;
use Fusio\Engine\ProcessorInterface;
use Fusio\Engine\RequestInterface;
use Fusio\Engine\Response\FactoryInterface;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;


$actionName = 'lb_sf_snowflake';

// Execute the action using processor
try {
    $actionResponse = $processor->execute($actionName, $request, $context);
} catch (ActionNotFoundException $e) {
    throw new RuntimeException('Required action "' . $actionName . '" not found.', 0, $e);
} catch (FactoryResolveException $e) {
    throw new RuntimeException('Failed to resolve action "' . $actionName . '".', 0, $e);
}

// Get the response body
$responseData = $actionResponse->getBody();
// Parse the response - it might be a string (JSON) or already an array
if (!is_string($responseData) && !is_array($responseData) && !is_object($responseData)) {
    throw new RuntimeException('Unexpected response type from action: ' . gettype($responseData));
}

$result = null;
if (is_string($responseData)) {
    $result = json_decode($responseData, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new RuntimeException('Failed to decode JSON response: ' . json_last_error_msg());
    }
} elseif (is_array($responseData)) {
    // Response is already an array
    $result = $responseData;
} elseif (is_object($responseData)) {
    // Convert object to array
    $result = [
        'code' => $responseData->code ?? null,
        'errmsg' => $responseData->message ?? null,
        'data' => (array)($responseData->data ?? []),
    ];
}

if (!is_array($result)) {
    throw new RuntimeException('Unexpected response format from action, expected array.');
}
if (!isset($result['code']) || !isset($result['data'])) {
    throw new RuntimeException('Response from action is missing required fields.');
}
if ($result['code'] != 0) {
    // Action returned an error
    $errorMessage = $result['errmsg'] ?? 'Unknown error from action';
    throw new RuntimeException('Action error: ' . $errorMessage);
}
if (!isset($result['data']['id'])) {
    throw new RuntimeException('Response data is missing id field.');
}

$requestId = $result['data']['id'];
$logger->info('Request ID: ' . $requestId);

RequestChainStorage::set('X-Request-Id', $requestId);

return $response->build(200, [], [
    'requestId' => $requestId,
]);
