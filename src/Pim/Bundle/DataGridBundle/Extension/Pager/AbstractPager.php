<?php

namespace Pim\Bundle\DataGridBundle\Extension\Pager;

use Oro\Bundle\DataGridBundle\Extension\Pager\PagerInterface;

/**
 * Class AbstractPager, inspired by Oro\Bundle\DataGridBundle\Extension\Pager
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractPager implements \Countable, \Serializable, PagerInterface
{
    /**
     * @var integer
     */
    protected $page = 1;

    /**
     * @var integer
     */
    protected $maxPerPage = 0;

    /**
     * @var integer
     */
    protected $lastPage = 1;

    /**
     * @var integer
     */
    protected $nbResults = 0;

    /**
     * @var integer
     */
    protected $currentMaxLink = 1;

    /**
     * @var integer
     */
    protected $maxRecordLimit = false;

    /**
     * @var integer
     */
    protected $maxPageLinks = 10;

    /**
     * Constructor.
     *
     * @param integer $maxPerPage Number of records to display per page
     */
    public function __construct($maxPerPage = 10)
    {
        $this->setMaxPerPage($maxPerPage);
    }

    /**
     * Returns the current pager's max link.
     *
     * @return integer
     */
    public function getCurrentMaxLink()
    {
        return $this->currentMaxLink;
    }

    /**
     * Returns an array of page numbers to use in pagination links.
     *
     * @param integer $nbLinks The maximum number of page numbers to return
     *
     * @return array
     */
    public function getLinks($nbLinks = null)
    {
        if ($nbLinks == null) {
            $nbLinks = $this->getMaxPageLinks();
        }
        $links = [];
        $tmp   = $this->page - floor($nbLinks / 2);
        $check = $this->lastPage - $nbLinks + 1;

        $limit = 1;
        if ($check > 0) {
            $limit = $check;
        }

        $begin = 1;
        if ($tmp > 0) {
            $begin = $tmp > $limit ? $limit : $tmp;
        }

        $index = (int) $begin;
        while ($index < $begin + $nbLinks && $index <= $this->lastPage) {
            $links[] = $index++;
        }

        $this->currentMaxLink = 1;
        if (count($links)) {
            $this->currentMaxLink = $links[count($links) - 1];
        }

        return $links;
    }

    /**
     * Returns true if the current datasource requires pagination.
     *
     * @return boolean
     */
    public function haveToPaginate()
    {
        return $this->getMaxPerPage() && $this->getNbResults() > $this->getMaxPerPage();
    }

    /**
     * Returns the number of results.
     *
     * @return integer
     */
    public function getNbResults()
    {
        return (int) $this->nbResults;
    }

    /**
     * Sets the number of results.
     *
     * @param integer $nb
     */
    protected function setNbResults($number)
    {
        $this->nbResults = $number;
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
        return $this->lastPage;
    }

    /**
     * Sets the last page number.
     *
     * @param integer $page
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
     * @return integer
     */
    public function getPage()
    {
        return $this->page;
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
     * Returns the previous page.
     *
     * @return integer
     */
    public function getPreviousPage()
    {
        return max($this->getPage() - 1, $this->getFirstPage());
    }

    /**
     * Sets the current page.
     *
     * @param integer $page
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
     * @return integer
     */
    public function getMaxPerPage()
    {
        return $this->maxPerPage;
    }

    /**
     * Sets the maximum number of results per page.
     *
     * @param integer $max
     */
    public function setMaxPerPage($max)
    {
        if ($max > 0) {
            $this->maxPerPage = $max;
            if ($this->page == 0) {
                $this->page = 1;
            }
        } else {
            if ($max == 0) {
                $this->maxPerPage = 0;
                $this->page       = 0;
            } else {
                $this->maxPerPage = 1;
                if ($this->page == 0) {
                    $this->page = 1;
                }
            }
        }
    }

    /**
     * Returns the maximum number of page numbers.
     *
     * @return integer
     */
    public function getMaxPageLinks()
    {
        return $this->maxPageLinks;
    }

    /**
     * Sets the maximum number of page numbers.
     *
     * @param integer $maxPageLinks
     */
    public function setMaxPageLinks($maxPageLinks)
    {
        $this->maxPageLinks = $maxPageLinks;
    }

    /**
     * Returns true if on the first page.
     *
     * @return boolean
     */
    public function isFirstPage()
    {
        return 1 == $this->page;
    }

    /**
     * Returns true if on the last page.
     *
     * @return boolean
     */
    public function isLastPage()
    {
        return $this->page == $this->lastPage;
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return $this->getNbResults();
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        $vars = get_object_vars($this);

        return serialize($vars);
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized)
    {
        $array = unserialize($serialized);

        foreach ($array as $name => $values) {
            $this->$name = $values;
        }
    }
}
