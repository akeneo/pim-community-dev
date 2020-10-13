<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Elasticsearch\Sorter;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Sorter\Field\BaseFieldSorter;
use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidDirectionException;
use Akeneo\Pim\Enrichment\Component\Product\Query\Sorter\Directions;

final class EnrichmentSorter extends BaseFieldSorter
{
    public function addFieldSorter($field, $direction, $locale = null, $channel = null): void
    {
        $field = sprintf('rates.enrichment.%s.%s', $channel, $locale);

        switch ($direction) {
            case Directions::ASCENDING:
                $order = 'ASC';
                break;
            case Directions::DESCENDING:
                $order = 'DESC';
                break;
            default:
                throw InvalidDirectionException::notSupported($direction, static::class);
        }

        $sortClause = [
            $field => [
                'order'   => $order,
                'missing' => '_last',
                'unmapped_type' => 'keyword',
            ],
        ];

        $this->searchQueryBuilder->addSort($sortClause);
    }
}
