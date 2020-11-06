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
    public function __construct(int $maxPerPage = 10)
    {
        $this->setMaxPerPage($maxPerPage);
    }

    /**
     * Returns the number of results.
     *
     * @return int
     */
    public function getNbResults(): int
    {
        return (int) $this->nbResults;
    }

    /**
     * Sets the number of results.
     *
     * @param int $number
     */
    protected function setNbResults(int $number)
    {
        $this->nbResults = $number;
    }

    /**
     * Sets the last page number.
     *
     * @param int $page
     */
    protected function setLastPage(int $page)
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
    public function getPage(): int
    {
        return $this->page;
    }

    /**
     * Sets the current page.
     *
     * @param int $page
     */
    public function setPage(int $page): void
    {
        $this->page = (int) $page;

        if ($this->page <= 0) {
            $this->page = $this->getMaxPerPage() !== 0 ? 1 : 0;
        }
    }

    /**
     * Returns the maximum number of results per page.
     *
     * @return int
     */
    public function getMaxPerPage(): int
    {
        return $this->maxPerPage;
    }

    /**
     * Sets the maximum number of results per page.
     *
     * @param int $max
     */
    public function setMaxPerPage(int $max): void
    {
        if ($max > 0) {
            $this->maxPerPage = $max;
            if ($this->page === 0) {
                $this->page = 1;
            }
        } elseif ($max === 0) {
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
