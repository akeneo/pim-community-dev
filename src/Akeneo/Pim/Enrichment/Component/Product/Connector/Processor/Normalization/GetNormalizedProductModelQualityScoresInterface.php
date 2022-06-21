<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\Normalization;

interface GetNormalizedProductModelQualityScoresInterface
{
    public function __invoke(string $code, string $channel = null, array $locales = []): array;
}
