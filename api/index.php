<?php

declare(strict_types=1);

// Vercel serverless functions use a read-only filesystem except /tmp.
// Force Blade compiled views to writable temporary storage.
$compiledViewPath = $_ENV['VIEW_COMPILED_PATH'] ?? $_SERVER['VIEW_COMPILED_PATH'] ?? '/tmp/framework/views';
if (! is_dir($compiledViewPath)) {
    @mkdir($compiledViewPath, 0777, true);
}
putenv("VIEW_COMPILED_PATH={$compiledViewPath}");
$_ENV['VIEW_COMPILED_PATH'] = $compiledViewPath;
$_SERVER['VIEW_COMPILED_PATH'] = $compiledViewPath;

$autoloadPath = __DIR__.'/../vendor/autoload.php';
if (! file_exists($autoloadPath)) {
    http_response_code(500);
    header('Content-Type: text/plain; charset=UTF-8');
    echo 'Runtime bootstrap failed: missing Composer dependencies (vendor/autoload.php).';
    return;
}

$appKey = getenv('APP_KEY') ?: ($_ENV['APP_KEY'] ?? $_SERVER['APP_KEY'] ?? '');
if (! is_string($appKey) || trim($appKey) === '') {
    http_response_code(500);
    header('Content-Type: text/plain; charset=UTF-8');
    echo 'Runtime bootstrap failed: APP_KEY is not set. Add APP_KEY in Vercel Project Environment Variables.';
    return;
}

require __DIR__.'/../public/index.php';
