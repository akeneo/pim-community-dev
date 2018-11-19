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

namespace Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity;

/**
 * Read model representing a search result
 *
 * @author    Tamara Robichet <tamara.robichet@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SearchReferenceEntityResult
{
    private const ITEMS = 'items';
    private const TOTAL = 'total';

    /** @var ReferenceEntityItem[] */
    public $items;

    /** @var int */
    public $total;

    public function normalize(): array
    {
        return [
            self::ITEMS => array_map(function (ReferenceEntityItem $recordItem) {
                return $recordItem->normalize();
            }, $this->items),
            self::TOTAL   => $this->total,
        ];
    }
}
