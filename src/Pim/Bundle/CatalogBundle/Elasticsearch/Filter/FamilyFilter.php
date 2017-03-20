<?php

namespace Pim\Bundle\CatalogBundle\Elasticsearch\Filter;

use Pim\Component\Catalog\Exception\InvalidOperatorException;
use Pim\Component\Catalog\Query\Filter\FieldFilterHelper;
use Pim\Component\Catalog\Query\Filter\FieldFilterInterface;
use Pim\Component\Catalog\Query\Filter\Operators;

/**
 * Family filter for an Elasticsearch query
 *
 * @author    Anaël Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyFilter extends AbstractFieldFilter implements FieldFilterInterface
{
    const FAMILY_KEY = 'family';

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
     */
    public function addFieldFilter(
        $field,
        $operator,
        $value,
        $locale = null,
        $scope = null,
        $options = []
    ) {
        if (null === $this->searchQueryBuilder) {
            throw new \LogicException('The search query builder is not initialized in the filter.');
        }

        if (Operators::IS_EMPTY !== $operator && Operators::IS_NOT_EMPTY !== $operator) {
            $this->checkValue($field, $value);
        }

        switch ($operator) {
            case Operators::IN_LIST:
                $clause = [
                    'terms' => [
                        self::FAMILY_KEY => $value
                    ]
                ];

                $this->searchQueryBuilder->addFilter($clause);
                break;
            case Operators::NOT_IN_LIST:
                $clause = [
                    'terms' => [
                        self::FAMILY_KEY => $value
                    ]
                ];

                $this->searchQueryBuilder->addMustNot($clause);
                break;
            case Operators::IS_EMPTY:
                $clause = [
                    'exists' => ['field' => self::FAMILY_KEY]
                ];

                $this->searchQueryBuilder->addMustNot($clause);
                break;
            case Operators::IS_NOT_EMPTY:
                $clause = [
                    'exists' => ['field' => self::FAMILY_KEY]
                ];

                $this->searchQueryBuilder->addFilter($clause);
                break;
            default:
                throw InvalidOperatorException::notSupported($operator, static::class);
        }

        return $this;
    }

    /**
     * Check if value is valid
     *
     * @param string $field
     * @param mixed  $values
     */
    protected function checkValue($field, $values)
    {
        FieldFilterHelper::checkArray($field, $values, static::class);

        foreach ($values as $value) {
            FieldFilterHelper::checkIdentifier($field, $value, static::class);
        }
    }
}
