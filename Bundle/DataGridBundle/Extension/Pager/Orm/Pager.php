<?php

namespace Oro\Bundle\DataGridBundle\Extension\Pager\Orm;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;

use Oro\Bundle\DataGridBundle\Datasource\Orm\ProxyQueryInterface;

class Pager extends AbstractPager implements PagerInterface
{
    /**
     * @var QueryBuilder|null
     */
    protected $queryBuilder = null;

    /**
     * {@inheritdoc}
     */
    public function getNbResults()
    {
        return intval(parent::getNbResults());
    }

    /**
     * {@inheritdoc}
     */
    public function computeNbResult()
    {
        return $this->getQuery()->getTotalCount();
    }

    /**
     * @return ProxyQueryInterface
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * {@inheritdoc}
     */
    public function getResults($hydrationMode = Query::HYDRATE_OBJECT)
    {
        return $this->getQuery()->execute(array(), $hydrationMode);
    }

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $this->resetIterator();

        $this->setNbResults($this->computeNbResult());

        /** @var QueryBuilder $query */
        $query = $this->getQuery();

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
}
