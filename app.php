<?php

$loader = include_once __DIR__ . '/vendor/autoload.php';

use Composer\Autoload\ClassLoader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use FrostieDE\Silex\Assetic\AsseticDumpCommand;
use FrostieDE\Silex\AsseticServiceProvider;
use FrostieDE\Silex\EnvironmentServiceProvider;
use FrostieDE\Silex\VersionServiceProvider;
use Igorw\Silex\ConfigServiceProvider;
use Knp\Provider\ConsoleServiceProvider;
use Monolog\Logger;
use Silex\Application;
use Silex\Provider\MonologServiceProvider;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\TwigServiceProvider;

if($loader instanceof ClassLoader) {
    // Autoload annotations (see http://stackoverflow.com/a/31918150)
    AnnotationRegistry::registerLoader([$loader, 'loadClass']);
}

$app = new Application();

$app['dir'] = __DIR__;

$app->register(new EnvironmentServiceProvider());
$app->register(new ConfigServiceProvider(__DIR__ . '/app/config.yml'));

$envConfigFile = __DIR__ . '/app/config.' . $app['env'] . '.yml';

if(file_exists($envConfigFile)) {
    $app->register(new ConfigServiceProvider($envConfigFile));
}

$app->register(new VersionServiceProvider(), [
    'version.file' => __DIR__ . '/VERSION'
]);

$app->register(new MonologServiceProvider(), [
    'monolog.logfile' => __DIR__ . '/var/logs/app.log',
    'monolog.level' => $app['debug'] ? Logger::DEBUG : Logger::NOTICE,
    'monolog.use_error_handler' => true
]);

$app->register(new SessionServiceProvider());

$app->register(new TwigServiceProvider(), [
    'twig.path' => __DIR__ . '/app/templates',
    'twig.options' => [
        'cache' => $app['env'] === 'dev' ? false :  __DIR__ . '/var/cache/twig',
        'auto_reload' => true
    ],
    'twig.form.templates' => [
        'bootstrap_3_horizontal_layout.html.twig'
    ]
]);

$app->register(new AsseticServiceProvider(), [
    'assetic.options' => [
        'assets_path' => $app['dir'] . '/app/assets/',
        'web_path' => $app['dir'] . '/web/'
    ]
]);

/*
 * Console commands
 */
$app->register(new ConsoleServiceProvider(), [
    'console.name' => 'Your-Project CLI',
    'console.version' => $app['version'],
    'console.project_directory' => __DIR__
]);
$app['console']->add(new AsseticDumpCommand());

return $app;