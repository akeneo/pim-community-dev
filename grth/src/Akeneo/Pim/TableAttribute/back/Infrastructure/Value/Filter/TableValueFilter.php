<?php

declare(strict_types=1);

namespace Akeneo\Pim\TableAttribute\Infrastructure\Value\Filter;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Filter\Attribute\AbstractAttributeFilter;
use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidOperatorException;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\AttributeFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Validator\ElasticsearchFilterValidator;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository\TableConfigurationRepository;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnCode;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class TableValueFilter extends AbstractAttributeFilter
{
    private TableConfigurationRepository $tableConfigurationRepository;
    /** @var array<string, ColumnTypeFilter> */
    private array $columnFilters = [];

    public function __construct(
        TableConfigurationRepository $tableConfigurationRepository,
        ElasticsearchFilterValidator $filterValidator,
        iterable $columnFilters = [],
        array $supportedAttributeTypes = [],
        array $supportedOperators = []
    ) {
        $this->tableConfigurationRepository = $tableConfigurationRepository;
        $this->filterValidator = $filterValidator;
        $this->supportedAttributeTypes = $supportedAttributeTypes;
        $this->supportedOperators = $supportedOperators;

        /** @var ColumnTypeFilter $columnFilter */
        foreach ($columnFilters as $columnFilter) {
            $this->columnFilters[$columnFilter->supportedColumnType()] = $columnFilter;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function addAttributeFilter(
        AttributeInterface $attribute,
        $operator,
        $data,
        $locale = null,
        $channel = null,
        $options = []
    ) {
        if (null === $this->searchQueryBuilder) {
            throw new \LogicException('The search query builder is not initialized in the filter.');
        }

        if ($data !== []) {
            // If data is empty, we search on the entire table. In this case we allow to search
            // for all locale and scope, even if the attribute is localizable/scopable
            $this->checkLocaleAndChannel($attribute, $locale, $channel);
        }

        $attributePath = \sprintf('table_values.%s', $attribute->getCode());

        $value = $data['value'] ?? null;
        $columnCode = $data['column'] ?? null;
        $rowCode = $data['row'] ?? null;

        if (null === $columnCode) {
            return $this->addAttributeFilterOnEntireTable($attribute, $attributePath, $operator, $locale, $channel);
        }

        $tableConfiguration = $this->tableConfigurationRepository->getByAttributeCode($attribute->getCode());
        $column = $tableConfiguration->getColumnByCode(ColumnCode::fromString($columnCode));
        /** @var ?ColumnTypeFilter $columnFilter */
        $columnFilter = $this->columnFilters[$column->dataType()->asString()] ?? null;
        if (null === $columnFilter) {
            throw new \InvalidArgumentException(sprintf(
                'The \'%s\' column with \'%s\' type is not supported',
                $columnCode,
                $column->dataType()->asString()
            ));
        }

        if (!$columnFilter->supportsOperator($operator)) {
            throw InvalidOperatorException::notSupported($operator, self::class);
        }

        $columnFilter->addFilter(
            $this->searchQueryBuilder,
            $attribute->getCode(),
            $operator,
            $column,
            $tableConfiguration->getFirstColumnCode()->equals($column->code()),
            $rowCode,
            $locale,
            $channel,
            $value
        );

        return $this;
    }

    private function addAttributeFilterOnEntireTable(
        AttributeInterface $attribute,
        string $attributePath,
        $operator,
        $locale = null,
        $channel = null
    ): AttributeFilterInterface {
        $filters = [
            ['exists' => ['field' => \sprintf('%s.row', $attributePath)]],
        ];
        if (null !== $locale) {
            $filters[] = ['term' => [\sprintf('%s.locale', $attributePath) => $locale]];
        }
        if (null !== $channel) {
            $filters[] = ['term' => [\sprintf('%s.channel', $attributePath) => $channel]];
        }

        $clause = [
            'nested' => [
                'path' => \sprintf('table_values.%s', $attribute->getCode()),
                'query' => [
                    'bool' => [
                        'filter' => $filters,
                    ],
                ],
                'ignore_unmapped' => true,
            ],
        ];

        if (Operators::IS_EMPTY === $operator) {
            $this->searchQueryBuilder->addMustNot($clause);
            $attributeInEntityClauses = [
                [
                    'terms' => [
                        self::ATTRIBUTES_FOR_THIS_LEVEL_ES_ID => [$attribute->getCode()],
                    ],
                ],
                [
                    'terms' => [
                        self::ATTRIBUTES_OF_ANCESTORS_ES_ID => [$attribute->getCode()],
                    ],
                ],
            ];

            $this->searchQueryBuilder->addFilter([
                'bool' => [
                    'should' => $attributeInEntityClauses,
                    'minimum_should_match' => 1,
                ],
            ]);

            return $this;
        }
        if (Operators::IS_NOT_EMPTY === $operator) {
            $this->searchQueryBuilder->addFilter($clause);

            return $this;
        }

        throw InvalidOperatorException::notSupported($operator, self::class);
    }
}
