<?php

namespace Pim\Bundle\DataGridBundle\Extension\Pager\Orm;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\DataGridBundle\ORM\Query\QueryCountCalculator;
use Oro\Bundle\DataGridBundle\Extension\Pager\PagerInterface;
use Pim\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource as PimOrmDatasource;
use Pim\Bundle\DataGridBundle\Extension\Pager\AbstractPager;

/**
 * Doctrine ORM pager
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Pager extends AbstractPager implements PagerInterface
{
    /**
     * @var QueryBuilder
     */
    protected $qb;

    /**
     * @var array
     */
    protected $parameters = [];

    /**
     * Constructor
     *
     * @param integer      $maxPerPage
     * @param QueryBuilder $qb
     */
    public function __construct($maxPerPage = 10, QueryBuilder $qb = null)
    {
        $this->qb = $qb;
        parent::__construct($maxPerPage);
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

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        $vars = get_object_vars($this);
        unset($vars['qb']);

        return serialize($vars);
    }

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $this->setNbResults($this->computeNbResult());

        /** @var QueryBuilder $query */
        $query = $this->getQueryBuilder();

        $query->setFirstResult(null);
        $query->setMaxResults(null);

        if (0 == $this->getPage() || 0 == $this->getMaxPerPage() || 0 == $this->getNbResults()) {
            $this->setLastPage(0);
        } else {
            $offset = ($this->getPage() - 1) * $this->getMaxPerPage();

            $this->setLastPage(ceil($this->getNbResults() / $this->getMaxPerPage()));

            $query->setFirstResult($offset);
            $query->setMaxResults($this->getMaxPerPage());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function computeNbResult()
    {
        $qb = clone $this->getQueryBuilder();

        $rootAlias  = $qb->getRootAlias();
        $rootField  = $rootAlias.'.id';
        $qb->groupBy($rootField);

        $qb->setFirstResult(null)
            ->setMaxResults(null)
            ->resetDQLPart('orderBy');

        PimOrmDatasource::removeExtraParameters($qb);

        $query = $qb->getQuery();

        return QueryCountCalculator::calculateCount($query);
    }
}
