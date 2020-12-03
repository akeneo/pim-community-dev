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
        $scoreField = sprintf('data_quality_insights.scores.%s.%s', $channel, $locale);
        $enrichmentField = sprintf('rates.enrichment.%s.%s', $channel, $locale);
        $consistencyField = sprintf('rates.consistency.%s.%s', $channel, $locale);

        switch ($direction) {
            case Directions::ASCENDING:
                $order = 'ASC';
                $nullValueReplacement = 99;
                break;
            case Directions::DESCENDING:
                $order = 'DESC';
                $nullValueReplacement = 0;
                break;
            default:
                throw InvalidDirectionException::notSupported($direction, static::class);
        }

        $sortClause = [
            '_script' => [
                'type' => 'number',
                'script' => [
                    'source' => "if(doc.containsKey('$scoreField') && doc['$scoreField'].size() > 0) { return doc['$scoreField'].value } else { long avgScore = Math.round(((doc.containsKey('$enrichmentField') && doc['$enrichmentField'].size() > 0 ? Integer.parseInt(doc['$enrichmentField'].value) : 0) + (doc.containsKey('$consistencyField') && doc['$consistencyField'].size() > 0 ? Integer.parseInt(doc['$consistencyField'].value) : (doc.containsKey('$enrichmentField') && doc['$enrichmentField'].size() > 0 ? Integer.parseInt(doc['$enrichmentField'].value) : 0))) / 2); return avgScore > 0 ? avgScore : $nullValueReplacement;}"
                ],
                'order' => $order,
            ],
        ];

        $this->searchQueryBuilder->addSort($sortClause);

        return $this;
    }
}
