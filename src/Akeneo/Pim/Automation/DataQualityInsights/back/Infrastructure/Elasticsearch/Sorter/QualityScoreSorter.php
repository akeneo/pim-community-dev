<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Elasticsearch\Sorter;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Sorter\Field\BaseFieldSorter;
use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidDirectionException;
use Akeneo\Pim\Enrichment\Component\Product\Query\Sorter\Directions;
use Akeneo\Pim\Enrichment\Component\Product\Query\Sorter\FieldSorterInterface;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class QualityScoreSorter extends BaseFieldSorter
{
    public function addFieldSorter($field, $direction, $locale = null, $channel = null): FieldSorterInterface
    {
        $field = sprintf('data_quality_insights.scores.%s.%s', $channel, $locale);

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

        return $this;
    }
}
