<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter;

use Doctrine\MongoDB\Query\Expr;
use Pim\Bundle\CatalogBundle\Doctrine\InvalidArgumentException;
use Pim\Bundle\CatalogBundle\Doctrine\Query\Operators;
use Pim\Bundle\CatalogBundle\Doctrine\Query\FieldFilterInterface;

/**
 * Family filter
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyFilter extends AbstractFilter implements FieldFilterInterface
{
    /** @var array */
    protected $supportedFields;

    /**
     * Instanciate the filter
     *
     * @param array $supportedFields
     * @param array $supportedOperators
     */
    public function __construct(
        array $supportedFields = [],
        array $supportedOperators = []
    ) {
        $this->supportedFields    = $supportedFields;
        $this->supportedOperators = $supportedOperators;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsField($field)
    {
        return in_array($field, $this->supportedFields);
    }

    /**
     * {@inheritdoc}
     */
    public function addFieldFilter($field, $operator, $value, $locale = null, $scope = null)
    {
        if (Operators::IS_EMPTY !== $operator) {
            $this->checkValue($field, $value);
        }

        if (Operators::IN_LIST === $operator) {
            $expr = new Expr();
            $this->qb->addAnd(
                $expr->field($field)->in($value)
            );
        } elseif (Operators::NOT_IN_LIST === $operator) {
            $this->qb->field($field)->notIn($value);
        } elseif (Operators::IS_EMPTY === $operator) {
            $expr = new Expr();
            $this->qb->addAnd(
                $expr->field($field)->exists(false)
            );
        }

        return $this;
    }

    /**
     * Check if value is valid
     *
     * @param string $field
     * @param mixed  $value
     */
    protected function checkValue($field, $value)
    {
        if (!is_array($value)) {
            throw InvalidArgumentException::arrayExpected($field, 'filter', 'family');
        }

        foreach ($value as $family) {
            if (!is_integer($family)) {
                throw InvalidArgumentException::integerExpected($field, 'filter', 'family');
            }
        }
    }
}
