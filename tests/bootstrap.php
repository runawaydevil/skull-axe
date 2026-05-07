<?php
declare(strict_types=1);

$loader = dirname(__DIR__).'/vendor/autoload.php';
if (is_readable($loader)) {
	require_once $loader;
}
