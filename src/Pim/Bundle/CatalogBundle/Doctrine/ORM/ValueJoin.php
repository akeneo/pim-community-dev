<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM;

use Doctrine\ORM\QueryBuilder;
use Pim\Bundle\CatalogBundle\Context\CatalogContext;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;

/**
 * Join utils class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ValueJoin
{
    /**
     * QueryBuilder
     * @var QueryBuilder
     */
    protected $qb;

    /**
     * @param QueryBuilder $qb
     */
    public function __construct(QueryBuilder $qb)
    {
        $this->qb = $qb;
    }

    /**
     * Prepare join to attribute condition with current locale and scope criterias
     *
     * @param AbstractAttribute $attribute the attribute
     * @param string            $joinAlias the value join alias
     * @param array             $context   the join context used for locale and scope
     *
     * @return string
     */
    public function prepareCondition(AbstractAttribute $attribute, $joinAlias, $context)
    {
        $condition = $joinAlias.'.attribute = '.$attribute->getId();

        if ($attribute->isLocalizable()) {
            $condition .= ' AND '.$joinAlias.'.locale = '.$this->qb->expr()->literal($context['locale']);
        }
        if ($attribute->isScopable()) {
            $condition .= ' AND '.$joinAlias.'.scope = '.$this->qb->expr()->literal($context['scope']);
        }

        return $condition;
    }
}
