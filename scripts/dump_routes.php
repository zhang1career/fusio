<?php
// dumps all routes from the project's routing parser to help debug "Unknown location"
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../cache/container.php';

try {
    $container = new ProjectServiceContainer();

    echo "Parameters:\n";
    $keys = ['psx_url', 'psx_dispatch', 'fusio_tenant_id'];
    foreach ($keys as $k) {
        try {
            $v = $container->getParameter($k);
        } catch (Exception $e) {
            $v = '(not set)';
        }
        echo sprintf("  %s = %s\n", $k, $v);
    }

    echo "\nRoutes:\n";
    $parser = $container->get(\PSX\Framework\Loader\RoutingParserInterface::class);
    $collection = $parser->getCollection();

    $i = 0;
    foreach ($collection as $row) {
        $methods = is_array($row[0]) ? implode(',', $row[0]) : (string)$row[0];
        $path = $row[1] ?? '(no path)';
        $source = $row[2] ?? '(no source)';
        // source can be array like [controller, method]
        if (is_array($source)) {
            $source = json_encode($source);
        }
        printf("%4d | %-20s | %-40s | %s\n", ++$i, $methods, $path, $source);
    }

    echo "\nTotal routes: " . $collection->count() . "\n";
} catch (Throwable $e) {
    echo "ERROR: " . get_class($e) . " - " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}

