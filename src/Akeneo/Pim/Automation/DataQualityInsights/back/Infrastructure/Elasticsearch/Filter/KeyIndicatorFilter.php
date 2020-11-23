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
class KeyIndicatorFilter extends AbstractFieldFilter implements FieldFilterInterface
{
    private string $keyIndicatorIdentifier;

    public function __construct(string $filterName, string $keyIndicatorIdentifier)
    {
        $this->supportedFields = [$filterName];
        $this->supportedOperators = ['='];
        $this->keyIndicatorIdentifier = $keyIndicatorIdentifier;
    }

    /**
     * {@inheritdoc}
     */
    public function addFieldFilter($field, $operator, $value, $locale = null, $channel = null, $options = [])
    {
        if (null === $this->searchQueryBuilder) {
            throw new \LogicException('The search query builder is not initialized in the filter.');
        }

        if (!is_bool($value)) {
            throw InvalidPropertyTypeException::booleanExpected($field, static::class, $value);
        }

        $clause = [
            'term' => [
                sprintf('data_quality_insights.key_indicators.%s.%s.%s', $channel, $locale, $this->keyIndicatorIdentifier) => $value,
            ],
        ];

        $this->searchQueryBuilder->addFilter($clause);

        return $this;
    }
}
