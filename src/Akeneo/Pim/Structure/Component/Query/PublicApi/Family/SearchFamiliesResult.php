<?php

namespace Akeneo\Pim\Structure\Component\Query\PublicApi\Family;

use Webmozart\Assert\Assert;

class SearchFamiliesResult
{
    public function __construct(
        private array $items,
        private int $matchesCount,
    ) {
        Assert::allIsInstanceOf($items, Family::class);
        Assert::greaterThanEq($matchesCount, 0);
    }

    public function normalize(): array
    {
        return [
            'matches_count' => $this->matchesCount,
            'items' => array_map(
                static fn (Family $family) => $family->normalize(),
                $this->items,
            ),
        ];
    }
}
