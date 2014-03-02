<?php

namespace Pim\Bundle\FlexibleEntityBundle\Doctrine\MongoDBODM;

use Doctrine\ODM\MongoDB\Query\Builder as QueryBuilder;
use Pim\Bundle\FlexibleEntityBundle\Model\AbstractAttribute;
use Pim\Bundle\FlexibleEntityBundle\Exception\FlexibleQueryException;
use Pim\Bundle\FlexibleEntityBundle\Doctrine\FlexibleQueryBuilderInterface;

/**
 * Aims to customize a query builder to add useful shortcuts which allow to easily select, filter or sort a flexible
 * entity values
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FlexibleQueryBuilder implements FlexibleQueryBuilderInterface
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
     * Get query builder
     *
     * @param QueryBuilder $qb
     *
     * @return FlexibleQueryBuilder
     */
    public function setQueryBuilder(QueryBuilder $qb)
    {
        $this->qb = $qb;

        return $this;
    }

    /**
     * Get query builder
     *
     * @return QueryBuilder
     */
    public function getQueryBuilder()
    {
        return $this->qb;
    }

    /**
     * {@inheritdoc}
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * {@inheritdoc}
     */
    public function setLocale($code)
    {
        $this->locale = $code;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * {@inheritdoc}
     */
    public function setScope($code)
    {
        $this->scope = $code;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addAttributeFilter(AbstractAttribute $attribute, $operator, $value)
    {
        throw new \RuntimeException("Not implemented yet ! ".__CLASS__."::".__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function addFieldFilter($field, $operator, $value)
    {
        throw new \RuntimeException("Not implemented yet ! ".__CLASS__."::".__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function addAttributeSorter(AbstractAttribute $attribute, $direction)
    {
        throw new \RuntimeException("Not implemented yet ! ".__CLASS__."::".__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function addFieldSorter($field, $direction)
    {
        throw new \RuntimeException("Not implemented yet ! ".__CLASS__."::".__METHOD__);
    }
}
