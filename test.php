<?php

require __DIR__.'/vendor/autoload.php';

$kernel = new AppKernel('dev', true);
$kernel->boot();

$test = [
    'identifier' => 'coucou',
    'labels' => [
        'coucou',
    ]
];

$validator = new \Akeneo\EnrichedEntity\back\Infrastructure\Validation\EnrichedEntity\RawDataValidator();
$violations = $validator->validate($test);

dump($violations);
