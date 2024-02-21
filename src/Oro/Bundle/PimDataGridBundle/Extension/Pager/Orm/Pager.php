<?php

namespace Oro\Bundle\PimDataGridBundle\Extension\Pager\Orm;

use Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\QueryBuilderUtility;
use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\DataGridBundle\Extension\Pager\PagerInterface;
use Oro\Bundle\DataGridBundle\ORM\Query\QueryCountCalculator;
use Oro\Bundle\PimDataGridBundle\Extension\Pager\AbstractPager;

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
     * Constructor
     *
     * @param int          $maxPerPage
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
    public function init()
    {
        $this->setNbResults($this->computeNbResult());

        /** @var QueryBuilder $query */
        $query = $this->getQueryBuilder();

        $query->setFirstResult(null);
        $query->setMaxResults(null);

        if (0 === $this->getPage() || 0 === $this->getMaxPerPage() || 0 === $this->getNbResults()) {
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

        $qb->setFirstResult(null)
            ->setMaxResults(null)
            ->resetDQLPart('orderBy');

        QueryBuilderUtility::removeExtraParameters($qb);

        $query = $qb->getQuery();

        return QueryCountCalculator::calculateCount($query);
    }
}
