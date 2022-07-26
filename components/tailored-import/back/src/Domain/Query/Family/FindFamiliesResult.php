<?php

namespace Akeneo\Platform\TailoredImport\Domain\Query\Family;

use Akeneo\Pim\Structure\Family\ServiceAPI\Query\FamilyWithLabels;
use Webmozart\Assert\Assert;

class FindFamiliesResult
{
    private int $matchesCount;

    /** @var FamilyWithLabels[] */
    private array $items;

    public function __construct(array $items, int $matchesCount)
    {
        Assert::allIsInstanceOf($items, FamilyWithLabels::class);

        $this->items = $items;
        $this->matchesCount = $matchesCount;
    }

    public function getMatchesCount(): int
    {
        return $this->matchesCount;
    }

    public function getItems(): array
    {
        return $this->items;
    }

    public function normalize(): array
    {
        return [
            'matches_count' => $this->matchesCount,
            'items' => array_map(
                static fn (FamilyWithLabels $family) => $family->normalize(),
                $this->items,
            ),
        ];
    }
}
