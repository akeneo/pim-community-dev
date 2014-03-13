<?php

namespace Pim\Bundle\DataGridBundle\Extension\Pager\MongoDbOdm;

use Doctrine\ODM\MongoDB\Query\Builder as QueryBuilder;
use Oro\Bundle\DataGridBundle\Extension\Pager\PagerInterface;
use Oro\Bundle\DataGridBundle\Extension\Pager\AbstractPager;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

/**
 * MongoDB ODM pager
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
     * @var AclHelper
     */
    protected $aclHelper;

    /**
     * Constructor
     *
     * @param AclHelper    $aclHelper
     * @param integer      $maxPerPage
     * @param QueryBuilder $qb
     */
    public function __construct(AclHelper $aclHelper, $maxPerPage = 10, QueryBuilder $qb = null)
    {
        $this->qb = $qb;
        parent::__construct($maxPerPage);
        $this->aclHelper = $aclHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $this->resetIterator();
        $this->setNbResults($this->computeNbResult());

        $query = $this->getQueryBuilder();
        $query->limit(null)->skip(null);

        if (0 == $this->getPage() || 0 == $this->getMaxPerPage() || 0 == $this->getNbResults()) {
            $this->setLastPage(0);
        } else {
            $offset = ($this->getPage() - 1) * $this->getMaxPerPage();
            $this->setLastPage(ceil($this->getNbResults() / $this->getMaxPerPage()));
            $query->limit($this->getMaxPerPage())->skip($offset);
        }
    }

    /**
     * Calculates count
     *
     * @return int
     */
    public function computeNbResult()
    {
        $qb = clone $this->getQueryBuilder();
        $count = $qb->getQuery()->execute()->count();

        return $count;
    }

    /**
     * {@inheritdoc}
     */
    public function getResults($hydrationMode = Query::HYDRATE_OBJECT)
    {
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

    /**
     * {@inheritdoc}
     */
    protected function retrieveObject($offset)
    {
        return null;
    }
}
