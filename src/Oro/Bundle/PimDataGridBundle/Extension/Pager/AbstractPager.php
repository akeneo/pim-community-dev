<?php

namespace Oro\Bundle\PimDataGridBundle\Extension\Pager;

use Oro\Bundle\DataGridBundle\Extension\Pager\PagerInterface;

/**
 * Class AbstractPager, inspired by Oro\Bundle\DataGridBundle\Extension\Pager
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractPager implements PagerInterface
{
    /** @var int */
    protected $page = 1;

    /** @var int */
    protected $maxPerPage = 0;

    /** @var int */
    protected $lastPage = 1;

    /** @var int */
    protected $nbResults = 0;

    /**
     * Constructor.
     *
     * @param int $maxPerPage Number of records to display per page
     */
    public function __construct($maxPerPage = 10)
    {
        $this->setMaxPerPage($maxPerPage);
    }

    /**
     * Returns the number of results.
     *
     * @return int
     */
    public function getNbResults()
    {
        return (int) $this->nbResults;
    }

    /**
     * Sets the number of results.
     *
     * @param int $number
     */
    protected function setNbResults($number)
    {
        $this->nbResults = $number;
    }

    /**
     * Sets the last page number.
     *
     * @param int $page
     */
    protected function setLastPage($page)
    {
        $this->lastPage = $page;

        if ($this->getPage() > $page) {
            $this->setPage($page);
        }
    }

    /**
     * Returns the current page.
     *
     * @return int
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * Sets the current page.
     *
     * @param int $page
     */
    public function setPage($page)
    {
        $this->page = intval($page);

        if ($this->page <= 0) {
            $this->page = $this->getMaxPerPage() ? 1 : 0;
        }
    }

    /**
     * Returns the maximum number of results per page.
     *
     * @return int
     */
    public function getMaxPerPage()
    {
        return $this->maxPerPage;
    }

    /**
     * Sets the maximum number of results per page.
     *
     * @param int $max
     */
    public function setMaxPerPage($max)
    {
        if ($max > 0) {
            $this->maxPerPage = $max;
            if ($this->page === 0) {
                $this->page = 1;
            }
        } else {
            if ($max === 0) {
                $this->maxPerPage = 0;
                $this->page = 0;
            } else {
                $this->maxPerPage = 1;
                if ($this->page === 0) {
                    $this->page = 1;
                }
            }
        }
    }
}
