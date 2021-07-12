<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredExport\Domain\Query;

interface FindProductLabelsInterface
{
    public function byIdentifiers(array $productIdentifiers, string $channel, string $locale): array;
}
