<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Join;

use Doctrine\ORM\QueryBuilder;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;

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
     *
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
     * @param AttributeInterface $attribute the attribute
     * @param string             $joinAlias the value join alias
     * @param string             $locale    the locale
     * @param string             $scope     the scope
     *
     * @return string
     */
    public function prepareCondition(AttributeInterface $attribute, $joinAlias, $locale = null, $scope = null)
    {
        $condition = $joinAlias.'.attribute = '.$attribute->getId();

        if ($attribute->isLocalizable() && null === $locale) {
            throw new \InvalidArgumentException(
                sprintf('Cannot prepare condition on localizable attribute "%s" without locale', $attribute->getCode())
            );
        }
        if ($attribute->isLocalizable()) {
            $condition .= ' AND '.$joinAlias.'.locale = '.$this->qb->expr()->literal($locale);
        }

        if ($attribute->isScopable() && null === $scope) {
            throw new \InvalidArgumentException(
                sprintf('Cannot prepare condition on scopable attribute "%s" without scope', $attribute->getCode())
            );
        }
        if ($attribute->isScopable()) {
            $condition .= ' AND '.$joinAlias.'.scope = '.$this->qb->expr()->literal($scope);
        }

        return $condition;
    }
}
