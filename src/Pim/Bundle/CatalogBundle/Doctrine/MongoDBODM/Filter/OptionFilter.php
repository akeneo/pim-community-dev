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
 *
 * TODO : avoid to extend entity filter
 */
class OptionFilter extends EntityFilter
{
    /**
     * {@inheritdoc}
     */
    public function supportsAttribute(AbstractAttribute $attribute)
    {
        return $attribute->getAttributeType() === 'pim_catalog_simpleselect';
    }

    /**
     * {@inheritdoc}
     */
    public function supportsOperator($operator)
    {
        return in_array($operator, ['IN', 'EMPTY']);
    }

    /**
     * {@inheritdoc}
     */
    public function addAttributeFilter(AbstractAttribute $attribute, $operator, $value)
    {
        $field = ProductQueryUtility::getNormalizedValueFieldFromAttribute($attribute, $this->context);
        $field = sprintf('%s.%s', ProductQueryUtility::NORMALIZED_FIELD, $field);
        $field = sprintf('%s.id', $field);

        if (in_array('empty', $value)) {
            unset($value[array_search('empty', $value)]);

            $expr = new Expr();
            $expr = $expr->field($field)->exists(false);
            $this->qb->addOr($expr);
        }

        if (count($value) > 0) {
            $value = array_map('intval', $value);
            $expr = new Expr();
            $expr->field($field)->in($value);

            $this->qb->addOr($expr);
        }

        return $this;
    }
}
