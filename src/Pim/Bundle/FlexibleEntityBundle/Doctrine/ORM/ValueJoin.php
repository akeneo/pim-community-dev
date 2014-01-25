<?php

namespace Pim\Bundle\FlexibleEntityBundle\Doctrine\ORM;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query\Expr\Join;
use Pim\Bundle\FlexibleEntityBundle\Model\AbstractAttribute;
use Pim\Bundle\FlexibleEntityBundle\Exception\FlexibleQueryException;

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
     * Locale code
     * @var string
     */
    protected $locale;

    /**
     * Scope code
     * @var string
     */
    protected $scope;

    /**
     * Instanciate a filter
     *
     * @param QueryBuilder $qb
     * @param string       $locale
     * @param scope        $scope
     */
    public function __construct(QueryBuilder $qb, $locale, $scope)
    {
        $this->qb     = $qb;
        $this->locale = $locale;
        $this->scope  = $scope;
    }

    /**
     * Prepare join to attribute condition with current locale and scope criterias
     *
     * @param AbstractAttribute $attribute the attribute
     * @param string            $joinAlias the value join alias
     *
     * @throws FlexibleQueryException
     *
     * @return string
     */
    public function prepareCondition(AbstractAttribute $attribute, $joinAlias)
    {
        $condition = $joinAlias.'.attribute = '.$attribute->getId();

        if ($attribute->isTranslatable()) {
            if ($this->locale === null) {
                throw new FlexibleQueryException('Locale must be configured');
            }
            $condition .= ' AND '.$joinAlias.'.locale = '.$this->qb->expr()->literal($this->locale);
        }
        if ($attribute->isScopable()) {
            if ($this->scope === null) {
                throw new FlexibleQueryException('Scope must be configured');
            }
            $condition .= ' AND '.$joinAlias.'.scope = '.$this->qb->expr()->literal($this->scope);
        }

        return $condition;
    }

}
