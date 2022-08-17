<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredImport\Domain\Query\Family;

use Akeneo\Pim\Structure\Family\ServiceAPI\Query\FamilyWithLabels;
use Webmozart\Assert\Assert;

class FindFamiliesResult
{
    /**
     * @param $items FamilyWithLabels[]
     */
    public function __construct(
        private array $items,
        private int $matchesCount,
    ) {
        Assert::allIsInstanceOf($items, FamilyWithLabels::class);
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
