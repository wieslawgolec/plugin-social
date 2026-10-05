<?php
declare(strict_types=1);
$root = dirname(__DIR__);
if (is_file($root.'/vendor/autoload.php')) {
    require $root.'/vendor/autoload.php';
}
spl_autoload_register(static function (string $class) use ($root): void {
    $prefix = 'MauticPlugin\\MauticSocialBundle\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }
    $file = $root.'/'.str_replace('\\', '/', substr($class, strlen($prefix))).'.php';
    if (is_file($file)) {
        require_once $file;
    }
});
