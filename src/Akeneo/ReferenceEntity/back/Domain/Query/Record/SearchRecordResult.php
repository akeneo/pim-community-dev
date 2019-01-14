<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ReferenceEntity\Domain\Query\Record;

use Webmozart\Assert\Assert;

/**
 * Read model representing a search result
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SearchRecordResult
{
    private const ITEMS = 'items';
    private const MATCHES_COUNT = 'matches_count';
    private const TOTAL_COUNT = 'total_count';

    /** @var RecordItem[] */
    public $items;

    /** @var int */
    public $matchesCount;

    /** @var int */
    public $totalCount;

    public function __construct(array $recordItems, int $matchesCount, int $totalCount)
    {
        Assert::allIsInstanceOf($recordItems, RecordItem::class);
        $this->items = $recordItems;
        $this->matchesCount = $matchesCount;
        $this->totalCount = $totalCount;
    }

    public function normalize(): array
    {
        return [
            self::ITEMS         => array_map(function (RecordItem $recordItem) {
                return $recordItem->normalize();
            }, $this->items),
            self::MATCHES_COUNT => $this->matchesCount,
            self::TOTAL_COUNT => $this->totalCount,
        ];
    }
}
