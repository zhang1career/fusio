<?php

use Monolog\Logger;
use function Symfony\Component\DependencyInjection\Loader\Configurator\env;

return [

    // OAuth2 access token expiration settings. How long can you use an access token and the refresh token. After the
    // expiration a user either need to use a refresh token to extend the token or request a new token
    'fusio_expire_token'       => env('APP_TOKEN_EXPIRE')->string(),
    'fusio_expire_refresh'     => env('APP_REFRESH_EXPIRE')->string(),

    // Optional a tenant id of you Fusio instance. This can be used to run multiple clients on the same Fusio
    // installation. All database entries are separated by the provided tenant id
    'fusio_tenant_id'          => env('APP_TENANT_ID')->string(),

    // The secret key of a project. It is recommended to change this to another random value. This is used i.e. to
    // encrypt the connection credentials in the database. NOTE IF YOU CHANGE THE KEY FUSIO CAN NO LONGER READ ANY DATA
    // WHICH WAS ENCRYPTED BEFORE. BECAUSE OF THAT IT IS RECOMMENDED TO CHANGE THE KEY ONLY BEFORE THE INSTALLATION
    'fusio_project_key'        => env('APP_PROJECT_KEY')->string(),

    // Optional an array of action or connection classes which are not allowed to use
    'fusio_action_exclude'     => null,
    'fusio_connection_exclude' => null,

    // Points to the Fusio provider file which contains specific classes for the system. Please take a look at the
    // provider file for more information
    'fusio_provider'           => __DIR__ . '/provider.php',

    // Describes the default email which Fusio uses as from address
    'fusio_mail_sender'        => env('APP_MAIL_SENDER')->string(),

    // Indicates whether the database is enabled. If true it is possible to change the database schema through the
    // backend
    'fusio_database'           => true,

    // Indicates whether the marketplace is enabled. If yes it is possible to download and install other apps through
    // the backend
    'fusio_marketplace'        => true,

    // Base URL of the user center (no trailing slash), used when an operation has usability=1 (external JWT validation)
    'ext_user_center_url'      => env('EXT_USER_CENTER_URL')->default('')->string(),
    // Shared Redis connection used by paganini service discovery (empty REDIS_HOST disables discovery).
    // No ->default('tcp'): Symfony compiles that to default:tcp:REDIS_SCHEME and treats `tcp` as a %parameter% name.
    'redis_scheme'             => env('REDIS_SCHEME')->string(),
    'redis_host'               => env('REDIS_HOST')->default('')->string(),
    'redis_port'               => env('REDIS_PORT')->default('')->int(),
    // Key prefix (namespace) for service registration entries in Redis, e.g. `reg:serv:`.
    'redis_prefix_register_service' => env('REDIS_PREFIX_REGISTER_SERVICE')->default('')->string(),
    // When ext_user_center_url contains `://{{service_key}}`, resolve host via Redis (paganini). Plain URLs skip Redis.
    'ext_user_center_sd_memo_ttl_seconds' => env('EXT_USER_CENTER_SD_MEMO_TTL')->default('')->int(),
    // GET path for Bearer token validation; empty uses /api/user/me/validate (see FrameworkConfig::getUserCenterMePath)
    'ext_user_center_me_path'  => env('EXT_USER_CENTER_ME_PATH')->default('')->string(),

    // Snowflake ID HTTP API (POST /api/snowflake/id). Placeholders `://{{service_key}}` use Redis like ext_user_center_url.
    'ext_snowflake_url'        => env('EXT_SNOWFLAKE_URL')->default('')->string(),
    'ext_snowflake_access_key' => env('EXT_SNOWFLAKE_ACCESS_KEY')->default('')->string(),

    // The public url to the apps folder (i.e. http://acme.com/apps or http://apps.acme.com)
    'fusio_apps_url'           => env('APP_APPS_URL')->string(),

    // Location where the apps are persisted from the marketplace. By default this is the public dir to access the apps
    // directly, but it is also possible to specify a different folder
    'fusio_apps_dir'           => __DIR__ . '/public/apps',

    // The url to the psx public folder (i.e. http://api.acme.com or http://127.0.0.1/psx/public)
    'psx_url'                  => env('APP_URL')->string(),

    // The input path 'index.php/' or '' if every request is served to the index.php file
    'psx_dispatch'             => '',

    // Defines the current environment i.e. prod or dev
    'psx_env'                  => env('APP_ENV')->string(),

    // Whether the app runs in debug mode or not. If not error reporting is set to 0, also several caches are used if
    // the debug mode is false
    'psx_debug'                => env('APP_DEBUG')->bool(),

    // Database parameters which are used for the doctrine DBAL connection
    // http://docs.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/configuration.html
    'psx_connection'           => env('APP_CONNECTION')->string(),

    // Mailer connection which is used to send mails
    // https://symfony.com/doc/current/mailer.html#using-built-in-transports
    'psx_mailer'               => env('APP_MAILER')->string(),

    // Messenger transport configuration
    // https://symfony.com/doc/current/messenger.html#transports-async-queued-messages
    'psx_messenger'            => env('APP_MESSENGER')->string(),

    'psx_migration_namespace'  => 'App\\Migrations',

    // Optional an SDKgen access token which adds support for different SDK generators
    // https://sdkgen.app/
    'sdkgen_client_id'         => env('SDKGEN_CLIENT_ID')->string(),
    'sdkgen_client_secret'     => env('SDKGEN_CLIENT_SECRET')->string(),

    'psx_log_level'            => env('LOG_LEVEL', Logger::ERROR,)->int(),

    // Folder locations
    'psx_path_cache'           => __DIR__ . '/cache',
    'psx_path_log'             => '/var/log/project/fusio',
    'psx_path_public'          => __DIR__ . '/public',
    'psx_path_src'             => __DIR__ . '/src',

    // Supported writers
    'psx_supported_writer'     => [
        \PSX\Data\Writer\Json::class,
        \PSX\Data\Writer\Jsonp::class,
        \PSX\Data\Writer\Jsonx::class,
    ],

];
