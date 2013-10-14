<?php

namespace Oro\Bundle\DataGridBundle\Extension\Pager\Orm;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;

use Oro\Bundle\BatchBundle\ORM\Query\QueryCountCalculator;
use Oro\Bundle\DataGridBundle\Extension\Pager\PagerInterface;
use Oro\Bundle\DataGridBundle\Extension\Pager\AbstractPager;

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
    public function computeNbResult()
    {
        $qb    = clone $this->getQueryBuilder();
        $query = $qb->setFirstResult(null)
            ->setMaxResults(null)
            ->resetDQLPart('orderBy')
            ->getQuery();

        return QueryCountCalculator::calculateCount($query);
    }

    /**
     * {@inheritdoc}
     */
    public function getResults($hydrationMode = Query::HYDRATE_OBJECT)
    {
        return $this->getQueryBuilder()->getQuery()->execute(array(), $hydrationMode);
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
        $this->resetIterator();

        $this->setNbResults($this->computeNbResult());

        /** @var QueryBuilder $query */
        $query = $this->getQueryBuilder();

        $query->setFirstResult(null);
        $query->setMaxResults(null);

        if (count($this->getParameters()) > 0) {
            $query->setParameters($this->getParameters());
        }

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
    protected function retrieveObject($offset)
    {
        $queryForRetrieve = clone $this->getQueryBuilder();
        $queryForRetrieve
            ->setFirstResult($offset - 1)
            ->setMaxResults(1);

        $results = $queryForRetrieve->getQuery()->execute();

        return $results[0];
    }
}
