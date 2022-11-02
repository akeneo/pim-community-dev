<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->sets([__DIR__ . '/releaseV7.rector.php']);

    $relativePaths = [
        'components/performance-analytics',
    ];

    $absolutePaths = array_map(
        static fn (string $relativePath) => sprintf('%s%s%s', __DIR__, '/../../../', $relativePath),
        $relativePaths,
    );

    $rectorConfig->paths($absolutePaths);
};
