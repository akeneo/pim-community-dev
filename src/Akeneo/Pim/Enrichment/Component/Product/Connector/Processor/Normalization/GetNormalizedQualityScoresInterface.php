<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\Normalization;

interface GetNormalizedQualityScoresInterface
{
    public function __invoke(string $identifier, string $channel = null, array $locales = []): array;
}
