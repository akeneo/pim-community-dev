<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Elasticsearch\Filter;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Filter\Field\AbstractFieldFilter;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\FieldFilterInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class QualityScoreMultiLocalesFilter extends AbstractFieldFilter implements FieldFilterInterface
{
    public const FIELD = 'quality_score_multi_locales';

    public const OPERATOR_IN_AT_LEAST_ONE_LOCALE = 'IN AT LEAST ONE LOCALE';
    public const OPERATOR_IN_ALL_LOCALES = 'IN ALL LOCALES';

    public function __construct()
    {
        $this->supportedFields = [self::FIELD];
        $this->supportedOperators = [
            self::OPERATOR_IN_ALL_LOCALES,
            self::OPERATOR_IN_AT_LEAST_ONE_LOCALE,
        ];
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

        if (null === $channel) {
            throw InvalidPropertyException::dataExpected($field, 'a channel', static::class);
        }

        $values = array_map(fn ($value) => intval($value), $values);
        $applyOnAllSelectedLocales = $operator === self::OPERATOR_IN_ALL_LOCALES;

        $locales = $this->getLocalesFromOptions($options);
        $terms = [];
        foreach ($locales as $locale) {
            $terms[] = [
                'terms' => [sprintf('data_quality_insights.scores.%s.%s', $channel, $locale) => $values]
            ];
        }

        $clause = [
            'bool' => [
                $applyOnAllSelectedLocales ? 'must' : 'should' => $terms,
            ],
        ];

        $this->searchQueryBuilder->addFilter($clause);

        return $this;
    }

    private function getLocalesFromOptions(array $options): array
    {
        if (!array_key_exists('locales', $options)) {
            throw InvalidPropertyTypeException::arrayKeyExpected(self::FIELD, 'locales', static::class, $options);
        }

        if (!is_array($options['locales'])) {
            throw InvalidPropertyTypeException::arrayOfArraysExpected(self::FIELD, static::class, $options);
        }

        if (empty($options['locales'])) {
            throw InvalidPropertyException::dataExpected(self::FIELD, 'at least one locale', static::class);
        }

        return $options['locales'];
    }
}
