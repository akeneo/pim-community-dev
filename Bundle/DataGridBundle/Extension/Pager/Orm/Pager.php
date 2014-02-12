<?php

namespace Oro\Bundle\DataGridBundle\Extension\Pager\Orm;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;

use Akeneo\Bundle\BatchBundle\ORM\Query\QueryCountCalculator;
use Oro\Bundle\DataGridBundle\Extension\Pager\PagerInterface;
use Oro\Bundle\DataGridBundle\Extension\Pager\AbstractPager;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

class Pager extends AbstractPager implements PagerInterface
{
    /** @var QueryBuilder */
    protected $qb;

    /** @var array */
    protected $parameters = [];

    /** @var AclHelper */
    protected $aclHelper;

    public function __construct(AclHelper $aclHelper, $maxPerPage = 10, QueryBuilder $qb = null)
    {
        $this->qb = $qb;
        parent::__construct($maxPerPage);
        $this->aclHelper = $aclHelper;
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
     * Calculates count
     *
     * @return int
     */
    public function computeNbResult()
    {
        $qb    = clone $this->getQueryBuilder();
        $query = $qb->setFirstResult(null)
            ->setMaxResults(null)
            ->resetDQLPart('orderBy')
            ->getQuery();

        $query = $this->aclHelper->apply($query);

        return QueryCountCalculator::calculateCount($query);
    }

    /**
     * {@inheritdoc}
     */
    public function getResults($hydrationMode = Query::HYDRATE_OBJECT)
    {
        return $this->getQueryBuilder()->getQuery()->execute([], $hydrationMode);
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
     * Returns the current pager's parameter holder.
     *
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * Returns a parameter.
     *
     * @param string $name
     * @param mixed  $default
     *
     * @return mixed
     */
    public function getParameter($name, $default = null)
    {
        return isset($this->parameters[$name]) ? $this->parameters[$name] : $default;
    }

    /**
     * Checks whether a parameter has been set.
     *
     * @param string $name
     *
     * @return boolean
     */
    public function hasParameter($name)
    {
        return isset($this->parameters[$name]);
    }

    /**
     * Sets a parameter.
     *
     * @param string $name
     * @param mixed  $value
     */
    public function setParameter($name, $value)
    {
        $this->parameters[$name] = $value;
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
