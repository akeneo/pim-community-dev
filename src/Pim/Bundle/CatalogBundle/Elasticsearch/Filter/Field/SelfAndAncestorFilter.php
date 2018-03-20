<?php

declare(strict_types=1);

namespace Pim\Bundle\CatalogBundle\Elasticsearch\Filter\Field;

use Pim\Component\Catalog\Exception\InvalidOperatorException;
use Pim\Component\Catalog\Query\Filter\FieldFilterHelper;
use Pim\Component\Catalog\Query\Filter\Operators;

/**
 * An ancestor is a product model that is either a parent or a grand parent.
 * Look for documents having the given ancestor(s).
 *
 * Imagine the following tree:
 *      RPM
 *         \PM1
 *            \P11
 *            \P12
 *         \PM2
 *            \P21
 *
 * Using this filter with "IN LIST PM1" would return:
 *         \PM1
 *            \P11
 *            \P12
 *
 * Contrary to the ancestor filter, here PM1 itself is as well returned.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SelfAndAncestorFilter extends AbstractFieldFilter
{
    private const ANCESTOR_ID_ES_FIELD = 'ancestors.ids';

    /**
     * @param array                           $supportedFields
     * @param array                           $supportedOperators
     */
    public function __construct(
        array $supportedFields,
        array $supportedOperators
    ) {
        $this->supportedFields = $supportedFields;
        $this->supportedOperators = $supportedOperators;
    }

    /**
     * {@inheritdoc}
     */
    public function addFieldFilter($field, $operator, $values, $locale = null, $channel = null, $options = []): void
    {
        if (null === $this->searchQueryBuilder) {
            throw new \LogicException('The search query builder is not initialized in the filter.');
        }

        if (!$this->supportsOperator($operator)) {
            throw InvalidOperatorException::notSupported($operator, SelfAndAncestorFilter::class);
        }

        $this->checkValues($values);

        switch ($operator) {
            case Operators::IN_LIST:
                $selfClause = [
                    'terms' => [
                        'id' => $values,
                    ],
                ];
                $ancestorsClause = [
                    'terms' => [
                        self::ANCESTOR_ID_ES_FIELD => $values,
                    ],
                ];
                $this->searchQueryBuilder->addShould($selfClause);
                $this->searchQueryBuilder->addShould($ancestorsClause);
                break;
            case Operators::NOT_IN_LIST:
                $selfClause = [
                    'terms' => [
                        'id' => $values,
                    ]
                ];
                $ancestorsClause = [
                    'terms' => [
                        self::ANCESTOR_ID_ES_FIELD => $values,
                    ],
                ];
                $this->searchQueryBuilder->addMustNot($selfClause);
                $this->searchQueryBuilder->addMustNot($ancestorsClause);
                break;
        }
    }

    /**
     * Checks the value we want to filter on is valid
     *
     * @param $values
     */
    private function checkValues($values): void
    {
        FieldFilterHelper::checkArray(self::ANCESTOR_ID_ES_FIELD, $values, static::class);
        foreach ($values as $value) {
            FieldFilterHelper::checkString(self::ANCESTOR_ID_ES_FIELD, $value, static::class);
        }
    }
}
