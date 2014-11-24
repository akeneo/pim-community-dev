<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter;

use Doctrine\MongoDB\Query\Expr;
use Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\ProductQueryUtility;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;

/**
 * Simple option filter for MongoDB implementation
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class OptionFilter extends EntityFilter
{
    /**
     * {@inheritdoc}
     */
    public function addAttributeFilter(AbstractAttribute $attribute, $operator, $value)
    {
        $field = ProductQueryUtility::getNormalizedValueFieldFromAttribute($attribute, $this->context);
        $field = sprintf('%s.%s', ProductQueryUtility::NORMALIZED_FIELD, $field);
        $field = sprintf('%s.id', $field);

        // Case filter with value(s) and empty
        if (in_array('empty', $value) && count($value) > 1) {
            unset($value[array_search('empty', $value)]);

            $exprValues = new Expr();
            $value = array_map('intval', $value);
            $exprValues->field($field)->in($value);

            $exprEmpty = new Expr();
            $exprEmpty = $exprEmpty->field($field)->exists(false);

            $exprAnd = new Expr();
            $exprAnd->addOr($exprValues);
            $exprAnd->addOr($exprEmpty);

            $this->qb->addAnd($exprAnd);
        } else {
            if (in_array('empty', $value)) {
                unset($value[array_search('empty', $value)]);

                $expr = new Expr();
                $expr = $expr->field($field)->exists(false);
                $this->qb->addAnd($expr);
            } elseif (count($value) > 0) {
                $value = array_map('intval', $value);
                $expr = new Expr();
                $expr->field($field)->in($value);

                $this->qb->addAnd($expr);
            }
        }

        return $this;
    }
}
