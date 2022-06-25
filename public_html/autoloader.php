<?php
function autoload(string $className): void
{
    require_once __DIR__ . '/../' . $className . '.php';
}

spl_autoload_register('autoload');
