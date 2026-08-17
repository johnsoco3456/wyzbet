<?php

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

/*
|--------------------------------------------------------------------------
| Serve bundled public assets
|--------------------------------------------------------------------------
|
| This package keeps its public assets beside the Laravel application.
| When Apache rewrites an asset request to this front controller, return
| files from the known public asset folders instead of rendering a page.
|
*/
$requestPath = rawurldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
$assetFolders = ['back', 'frontend', 'global', 'storage', 'woocasino'];

foreach ($assetFolders as $assetFolder) {
    $prefix = '/' . $assetFolder . '/';

    if (strpos($requestPath, $prefix) !== 0) {
        continue;
    }

    $assetRoot = realpath(__DIR__ . DIRECTORY_SEPARATOR . $assetFolder);
    $assetFile = realpath(__DIR__ . DIRECTORY_SEPARATOR . ltrim($requestPath, '/'));

    if (
        $assetRoot !== false &&
        $assetFile !== false &&
        strpos($assetFile, $assetRoot . DIRECTORY_SEPARATOR) === 0 &&
        is_file($assetFile)
    ) {
        $extension = strtolower(pathinfo($assetFile, PATHINFO_EXTENSION));
        $contentTypes = [
            'css' => 'text/css; charset=UTF-8',
            'js' => 'application/javascript; charset=UTF-8',
            'json' => 'application/json; charset=UTF-8',
            'svg' => 'image/svg+xml',
            'woff' => 'font/woff',
            'woff2' => 'font/woff2',
            'ttf' => 'font/ttf',
            'eot' => 'application/vnd.ms-fontobject',
        ];

        header('Content-Type: ' . ($contentTypes[$extension] ?? mime_content_type($assetFile) ?: 'application/octet-stream'));
        header('Content-Length: ' . filesize($assetFile));
        readfile($assetFile);
        exit;
    }
}


/*
|--------------------------------------------------------------------------
| Check If Application Is Under Maintenance
|--------------------------------------------------------------------------
|
| If the application is maintenance / demo mode via the "down" command we
| will require this file so that any prerendered template can be shown
| instead of starting the framework, which could cause an exception.
|
*/

if (file_exists(__DIR__.'/casino/storage/framework/maintenance.php')) {
    require __DIR__.'/casino/storage/framework/maintenance.php';
}

/*
|--------------------------------------------------------------------------
| Register The Auto Loader
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader for
| this application. We just need to utilize it! We'll simply require it
| into the script here so we don't need to manually load our classes.
|
*/

require __DIR__.'/casino/vendor/autoload.php';

/*
|--------------------------------------------------------------------------
| Run The Application
|--------------------------------------------------------------------------
|
| Once we have the application, we can handle the incoming request using
| the application's HTTP kernel. Then, we will send the response back
| to this client's browser, allowing them to enjoy our application.
|
*/

$app = require_once __DIR__.'/casino/bootstrap/app.php';

$kernel = $app->make(Kernel::class);

$response = tap($kernel->handle(
    $request = Request::capture()
))->send();

$kernel->terminate($request, $response);
