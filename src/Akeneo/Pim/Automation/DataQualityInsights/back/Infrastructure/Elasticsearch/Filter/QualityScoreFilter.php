<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Elasticsearch\Filter;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Filter\Field\AbstractFieldFilter;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\FieldFilterInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class QualityScoreFilter extends AbstractFieldFilter implements FieldFilterInterface
{
    public function __construct()
    {
        $this->supportedFields = ['data_quality_insights_score'];
        $this->supportedOperators = ['IN'];
    }

    /**
     * {@inheritdoc}
     */
    public function addFieldFilter($field, $operator, $values, $locale = null, $channel = null, $options = [])
    {
        if (null === $this->searchQueryBuilder) {
            throw new \LogicException('The search query builder is not initialized in the filter.');
        }

        if (!is_array($values)) {
            throw InvalidPropertyTypeException::arrayExpected($field, static::class, $values);
        }

        $clause = [
            'terms' => [
                sprintf('data_quality_insights.scores.%s.%s', $channel, $locale) => $values
            ],
        ];

        $this->searchQueryBuilder->addFilter($clause);

        return $this;
    }
}
