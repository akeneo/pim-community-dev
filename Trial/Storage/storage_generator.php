<?php

require_once __DIR__ . '/StoragePathGenerator.php';

$generator = new \Akeneo\Trial\Storage\StoragePathGenerator();
$dirty = 'ce"ci   est mon su~per joli fichié De test OY22.txt';
$clean = 'file.test';

for ($i = 0; $i < 1000000; $i++) {
    /*echo "\n" .  */$generator->generate($dirty);
}
