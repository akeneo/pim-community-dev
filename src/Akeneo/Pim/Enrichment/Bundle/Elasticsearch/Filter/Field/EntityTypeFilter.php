<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Filter\Field;

use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidArgumentException;
use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidOperatorException;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\FieldFilterHelper;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\FieldFilterInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;

/**
 * Some PQBs are able to return objects of different types (eg, Product and Product models). In some cases it is useful
 * to filter only on one or the other entity type.
 *
 * Please note that this filter is mapped to the field "document_type" in Elasticsearch.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EntityTypeFilter extends AbstractFieldFilter implements FieldFilterInterface
{
    private const ES_FIELD = 'document_type';

    /**
     * @param array $supportedFields
     * @param array $supportedOperators
     */
    public function __construct(
        array $supportedFields = [],
        array $supportedOperators = []
    ) {
        $this->supportedFields = $supportedFields;
        $this->supportedOperators = $supportedOperators;
    }

    /**
     * {@inheritdoc}
     *
     * @throws InvalidArgumentException
     * @throws InvalidPropertyTypeException
     */
    public function addFieldFilter($field, $operator, $value, $locale = null, $channel = null, $options = [])
    {
        if (null === $this->searchQueryBuilder) {
            throw new \LogicException('The search query builder is not initialized in the filter.');
        }

        if (!in_array($field, $this->supportedFields)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Unsupported field name for entity filter, only "%s" are supported, "%s" given',
                    implode(',', $this->supportedFields),
                    $field
                )
            );
        }

        if (!in_array($operator, $this->supportedOperators)) {
            throw InvalidOperatorException::notSupported($operator, static::class);
        }

        $this->assertIsString($field, $value);

        $value = str_replace('\\', '\\\\', $value);

        $this->searchQueryBuilder->addFilter(
            [
                'query_string' => [
                    'default_field' => self::ES_FIELD,
                    'query'         => $value,
                ],
            ]
        );
    }

    private function assertIsString($field, $value): void
    {
        FieldFilterHelper::checkString($field, $value, static::class);
    }
}
