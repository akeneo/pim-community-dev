<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter;

use Doctrine\MongoDB\Query\Expr;
use Doctrine\ODM\MongoDB\Query\Builder as QueryBuilder;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\ProductQueryUtility;
use Pim\Bundle\CatalogBundle\Context\CatalogContext;

/**
 * Multi options filter for MongoDB
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class OptionsFilter extends EntityFilter
{
    /** @var QueryBuilder */
    protected $qb;

    /** @var CatalogContext */
    protected $context;

    /**
     * @param QueryBuilder   $qb      the query builder
     * @param CatalogContext $context the catalog context
     */
    public function __construct(QueryBuilder $qb, CatalogContext $context)
    {
        $this->qb      = $qb;
        $this->context = $context;
    }

    /**
     * {@inheritdoc}
     */
    public function addAttributeFilter(AbstractAttribute $attribute, $operator, $value)
    {
        $field = ProductQueryUtility::getNormalizedValueFieldFromAttribute($attribute, $this->context);
        $field = sprintf('%s.%s', ProductQueryUtility::NORMALIZED_FIELD, $field);
        $this->addFieldFilter($field, $operator, $value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addFieldFilter($field, $operator, $value)
    {
        $value = is_array($value) ? $value : [$value];

        if ($operator === 'NOT IN') {
            $this->qb->field($field)->notIn($value);
        } else {
            // Case filter with value(s) and empty
            if (in_array('empty', $value) && count($value) > 1) {
                unset($value[array_search('empty', $value)]);

                $exprValues = new Expr();
                $value = array_map('intval', $value);
                $exprValues->field($field.'.id')->in($value);

                $exprEmpty = new Expr();
                $exprEmpty = $exprEmpty->field($field)->exists(false);

                $exprAnd = new Expr();
                $exprAnd->addOr($exprValues);
                $exprAnd->addOr($exprEmpty);

                $this->qb->addAnd($exprAnd);

                $a = array();


            } else {
                if (in_array('empty', $value)) {
                    unset($value[array_search('empty', $value)]);

                    $expr = new Expr();
                    $expr = $expr->field($field)->exists(false);
                    $this->qb->addAnd($expr);
                }

                if (count($value) > 0) {
                    $expr = new Expr();
                    $value = array_map('intval', $value);
                    $expr->field($field.'.id')->in($value);
                    $this->qb->addAnd($expr);
                }
            }
        }

        return $this;
    }
}
