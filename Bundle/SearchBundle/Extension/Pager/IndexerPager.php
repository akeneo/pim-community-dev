<?php

namespace Oro\Bundle\SearchBundle\Extension\Pager;

use Doctrine\ORM\Query;

use Oro\Bundle\SearchBundle\Query\Result;

class IndexerPager
{
    /**
     * @var int
     */
    protected $maxPerPage = 10;

    /**
     * @var int
     */
    protected $page = 1;

    /**
     * @var int
     */
    protected $nbResults = 0;

    /**
     * @var Query
     */
    protected $query;

    /**
     * @param Query $query
     */
    public function setQuery($query)
    {
        $this->query = $query;
    }

    /**
     * Initialize the Pager.
     */
    public function init()
    {
        if (!$this->query) {
            throw new \LogicException('Indexer query must be set');
        }
    }

    /**
     * Returns the number of results.
     *
     * @return integer
     */
    public function getNbResults()
    {
        return $this->nbResults = $this->query->getTotalCount();
    }

    /**
     * Calculate first result based on page and max-per-page
     */
    protected function calculateFirstResult()
    {
        $maxPerPage = $this->getMaxPerPage();
        $page = $this->getPage();

        $this->query->setFirstResult($maxPerPage * ($page - 1));
    }

    /**
     * @param int $maxPerPage
     */
    public function setMaxPerPage($maxPerPage)
    {
        if ($maxPerPage > 0) {
            $this->maxPerPage = $maxPerPage;
            $this->query->setMaxResults($maxPerPage);
        } else {
            $this->maxPerPage = 0;
            $this->query->setMaxResults(Query::INFINITY);
        }

        $this->calculateFirstResult();
    }

    /**
     * @return int
     */
    public function getMaxPerPage()
    {
        return $this->maxPerPage;
    }

    /**
     * @param  int  $page
     * @return void
     */
    public function setPage($page)
    {
        $this->page = $page;

        $this->calculateFirstResult();
    }

    /**
     * @return int
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * Returns the previous page.
     *
     * @return int
     */
    public function getPreviousPage()
    {
        return max($this->getPage() - 1, $this->getFirstPage());
    }

    /**
     * Returns the next page.
     *
     * @return integer
     */
    public function getNextPage()
    {
        return min($this->getPage() + 1, $this->getLastPage());
    }

    /**
     * Returns the first page number.
     *
     * @return integer
     */
    public function getFirstPage()
    {
        return 1;
    }

    /**
     * Returns the last page number.
     *
     * @return integer
     */
    public function getLastPage()
    {
        return ceil($this->getNbResults() / $this->getMaxPerPage());
    }

    /**
     * @return boolean
     */
    public function haveToPaginate()
    {
        return $this->getMaxPerPage() && $this->getNbResults() > $this->getMaxPerPage();
    }

    /**
     * Returns an array of page numbers to use in pagination links.
     *
     * @deprecated Should not be used
     *
     * @param  integer $nbLinks The maximum number of page numbers to return
     * @return array
     */
    public function getLinks($nbLinks = null)
    {
        return array();
    }
}
