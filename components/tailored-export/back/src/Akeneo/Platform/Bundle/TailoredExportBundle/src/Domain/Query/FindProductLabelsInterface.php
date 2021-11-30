<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredExport\Domain\Query;

interface FindProductLabelsInterface
{
    /**
     * @param string[] $productIdentifiers
     * @return array<string, string>
     */
    public function byIdentifiers(array $productIdentifiers, string $channel, string $locale): array;
}
