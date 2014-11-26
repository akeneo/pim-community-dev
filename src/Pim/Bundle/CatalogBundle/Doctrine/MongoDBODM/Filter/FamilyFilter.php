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
        $this->checkValue($field, $value);

        $value = is_array($value) ? $value : [$value];

        if ($operator === Operators::NOT_IN_LIST) {
            $this->qb->field($field)->notIn($value);
        } else {
            // Case filter with value(s) and empty
            if (in_array('empty', $value) && count($value) > 1) {
                unset($value[array_search('empty', $value)]);
                $exprValues = new Expr();
                $exprValues->field($field)->in($value);

                $exprEmpty = new Expr();
                $exprEmpty = $exprEmpty->field($field)->exists(false);

                $exprAnd = new Expr();
                $exprAnd->addOr($exprValues);
                $exprAnd->addOr($exprEmpty);
                $this->qb->addAnd($exprAnd);
            } elseif (in_array('empty', $value)) {
                // TODO: fix this weird support of EMPTY operator
                unset($value[array_search('empty', $value)]);

                $expr = new Expr();
                $expr = $expr->field($field)->exists(false);
                $this->qb->addAnd($expr);
            } elseif (count($value) > 0) {
                $expr = new Expr();
                $expr->field($field)->in($value);
                $this->qb->addAnd($expr);
            }
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
            if ('empty' !== $family && !is_integer($family)) {
                throw InvalidArgumentException::integerExpected($field, 'filter', 'family');
            }
        }
    }
}
