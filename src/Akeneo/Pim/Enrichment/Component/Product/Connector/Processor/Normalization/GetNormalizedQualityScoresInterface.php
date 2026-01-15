<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\Normalization;

use Ramsey\Uuid\UuidInterface;

interface GetNormalizedQualityScoresInterface
{
    public function __invoke(string|UuidInterface $identifierOrUuid, ?string $channel = null, array $locales = []): array;
}
