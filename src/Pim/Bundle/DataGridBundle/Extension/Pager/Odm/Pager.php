<?php

namespace Pim\Bundle\DataGridBundle\Extension\Pager\Odm;

use Doctrine\ODM\MongoDB\Query\Builder as QueryBuilder;
use Oro\Bundle\DataGridBundle\Extension\Pager\PagerInterface;
use Oro\Bundle\DataGridBundle\Extension\Pager\AbstractPager;

/**
 * Our custom orm pager
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Pager extends AbstractPager implements PagerInterface
{
    /** @var QueryBuilder */
    protected $qb;

    public function __construct($maxPerPage = 10, QueryBuilder $qb = null)
    {
        $this->qb = $qb;
        parent::__construct($maxPerPage);
    }

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        // @TODO throw new \RuntimeException("Not implemented yet ! ".__CLASS__."::".__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function getResults($hydrationMode = Query::HYDRATE_OBJECT)
    {
        // @TODO throw new \RuntimeException("Not implemented yet ! ".__CLASS__."::".__METHOD__);
        return [];
    }

    /**
     * {@inheritdoc}
     */
    protected function retrieveObject($offset)
    {
        // @TODO throw new \RuntimeException("Not implemented yet ! ".__CLASS__."::".__METHOD__);
        return null;
    }

    /**
     * @param QueryBuilder $qb
     *
     * @return $this
     */
    public function setQueryBuilder(QueryBuilder $qb)
    {
        $this->qb = $qb;

        return $this;
    }

    /**
     * @return QueryBuilder
     */
    public function getQueryBuilder()
    {
        return $this->qb;
    }
}
