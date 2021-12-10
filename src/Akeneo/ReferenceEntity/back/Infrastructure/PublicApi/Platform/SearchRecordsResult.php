<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ReferenceEntity\Infrastructure\PublicApi\Platform;

use Webmozart\Assert\Assert;

class SearchRecordsResult
{
    /** @var Record[] */
    private array $items;

    public function __construct(array $items, private int $matchesCount)
    {
        Assert::allIsInstanceOf($items, Record::class);

        $this->items = $items;
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
                static fn (Record $record) => $record->normalize(),
                $this->items,
            ),
        ];
    }
}
