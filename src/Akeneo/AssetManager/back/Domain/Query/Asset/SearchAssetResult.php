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

namespace Akeneo\AssetManager\Domain\Query\Asset;

use Webmozart\Assert\Assert;

/**
 * Read model representing a search result
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SearchAssetResult
{
    private const ITEMS = 'items';
    private const MATCHES_COUNT = 'matches_count';
    private const TOTAL_COUNT = 'total_count';

    /**
     * @param AssetItem[] $assetItems
     */
    public function __construct(public array $assetItems, public int $matchesCount, public int $totalCount)
    {
        Assert::allIsInstanceOf($assetItems, AssetItem::class);
    }

    public function normalize(): array
    {
        return [
            self::ITEMS         => array_map(fn (AssetItem $assetItem) => $assetItem->normalize(), $this->assetItems),
            self::MATCHES_COUNT => $this->matchesCount,
            self::TOTAL_COUNT => $this->totalCount,
        ];
    }
}
