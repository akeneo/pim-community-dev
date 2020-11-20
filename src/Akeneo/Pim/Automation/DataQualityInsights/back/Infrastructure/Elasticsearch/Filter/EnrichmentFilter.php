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

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Elasticsearch\Filter;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Filter\Field\AbstractFieldFilter;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\FieldFilterInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;

class EnrichmentFilter extends AbstractFieldFilter implements FieldFilterInterface
{
    public function __construct()
    {
        $this->supportedFields = ['data_quality_insights_enrichment'];
        $this->supportedOperators = ['IN'];
    }

    /**
     * {@inheritdoc}
     */
    public function addFieldFilter($field, $operator, $value, $locale = null, $channel = null, $options = [])
    {
        if (null === $this->searchQueryBuilder) {
            throw new \LogicException('The search query builder is not initialized in the filter.');
        }

        if (!is_array($value)) {
            $value = [1,2,3,4,5];
            //throw InvalidPropertyTypeException::arrayExpected($field, static::class, $value);
        }

        $clause = [
            'terms' => [
                sprintf('rates.enrichment.%s.%s', $channel, $locale) => $value,
            ],
        ];

        $this->searchQueryBuilder->addFilter($clause);

        return $this;
    }
}
