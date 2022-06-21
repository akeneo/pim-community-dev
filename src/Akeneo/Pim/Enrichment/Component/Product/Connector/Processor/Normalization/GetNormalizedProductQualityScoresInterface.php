<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\Normalization;

use Ramsey\Uuid\UuidInterface;

interface GetNormalizedProductQualityScoresInterface
{
    public function __invoke(UuidInterface $productUuid, string $channel = null, array $locales = []): array;
}
