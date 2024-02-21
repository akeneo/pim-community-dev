<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Component\Query\PublicApi\Family;

final class Family
{
    /**
     * @param array<string, string> $labels
     * @params list<string> $attributeCodes
     */
    public function __construct(
        public readonly string $code,
        public readonly array $labels,
        public readonly array $attributeCodes,
    ) {
    }
}
