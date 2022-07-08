<?php

namespace Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Filter\Field;

use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidOperatorException;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\FieldFilterHelper;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Doctrine\DBAL\Connection;

/**
 * Id filter for an Elasticsearch query
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IdFilter extends AbstractFieldFilter
{
    public function __construct(
        array $supportedFields,
        array $supportedOperators,
        private string $prefix,
        private Connection $connection
    ) {
        $this->supportedFields = $supportedFields;
        $this->supportedOperators = $supportedOperators;
    }

    /**
     * {@inheritdoc}
     */
    public function addFieldFilter(
        $attribute,
        $operator,
        $value,
        $locale = null,
        $channel = null,
        $options = []
    ) {
        if (null === $this->searchQueryBuilder) {
            throw new \LogicException('The search query builder is not initialized in the filter.');
        }

        $this->checkValue($operator, $value);

        if (is_array($value)) {
            $value = array_map(
                function ($value) {
                    return (string) $this->prefix.$value;
                },
                $value
            );
        } else {
            $value = [(string)$this->prefix.$value];
        }

        $value = $this->addProductUuidsInValues($value);

        switch ($operator) {
            case Operators::EQUALS:
                $clause = [
                    'terms' => [
                        'id' => $value
                    ]
                ];
                $this->searchQueryBuilder->addFilter($clause);
                break;
            case Operators::NOT_EQUAL:
                $mustNotClause = [
                    'terms' => [
                        'id' => $value,
                    ],
                ];
                $filterClause = [
                    'exists' => [
                        'field' => 'id',
                    ],
                ];
                $this->searchQueryBuilder->addMustNot($mustNotClause);
                $this->searchQueryBuilder->addFilter($filterClause);
                break;
            case Operators::IN_LIST:
                $clause = [
                    'terms' => [
                        'id' => $value,
                    ],
                ];

                $this->searchQueryBuilder->addFilter($clause);
                break;
            case Operators::NOT_IN_LIST:
                $clause = [
                    'terms' => [
                        'id' => $value,
                    ],
                ];

                $this->searchQueryBuilder->addMustNot($clause);
                break;
            default:
                throw InvalidOperatorException::notSupported($operator, static::class);
        }

        return $this;
    }

    /**
     * Checks that the value is a number.
     *
     * @param string $operator
     * @param mixed  $value
     */
    protected function checkValue($operator, $value)
    {
        if (in_array($operator, [Operators::EQUALS, Operators::NOT_EQUAL])) {
            FieldFilterHelper::checkString('id', $value, static::class);
        } else {
            FieldFilterHelper::checkArray('id', $value, static::class);
            foreach ($value as $oneValue) {
                if (!is_string($oneValue)) {
                    throw InvalidPropertyTypeException::validArrayStructureExpected(
                        'id',
                        'one of the value is not string',
                        static::class,
                        $value
                    );
                }
            }
        }
    }

    private function addProductUuidsInValues(array $values): array
    {
        $ids = [];
        foreach ($values as $value) {
            if (\strpos($value, 'product_') === false || \strpos($value, 'product_model_') !== false) {
                continue;
            }

            $id = \str_replace('product_', '', $value);
            if (\is_numeric($id)) {
                $ids[] = $id;
            }
        }

        if ([] === $ids) {
            return $values;
        }

        $uuids = $this->connection->executeQuery(
            'SELECT BIN_TO_UUID(uuid) as uuid FROM pim_catalog_product WHERE id IN (:ids)',
            ['ids' => $ids],
            ['ids' => Connection::PARAM_STR_ARRAY]
        )->fetchFirstColumn();

        return \array_merge(
            $values,
            \array_map(static fn (string $uuid): string => \sprintf('product_%s', $uuid), \array_filter($uuids))
        );
    }
}
