<?php

declare(strict_types=1);

use Nelwhix\WhatsappPhpClient\Container;


it('creates an instance of container', function () {
    $dsn = 'sqlite:' . __DIR__ . '/../test.sqlite';
    $container = new Container('sqlite', $dsn);

    expect($container)->toBeInstanceOf(Container::class);
})->only();