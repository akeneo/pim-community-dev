<?php

namespace Oro\Bundle\DataGridBundle\Extension\Pager;

/**
 * Class AbstractPager
 * @package Oro\Bundle\DataGridBundle\Extension\Pager
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
abstract class AbstractPager implements \Iterator, \Countable, \Serializable, PagerInterface
{
    protected $page = 1;
    protected $maxPerPage = 0;
    protected $lastPage = 1;
    protected $nbResults = 0;
    protected $cursor = 1;
    protected $currentMaxLink = 1;
    protected $maxRecordLimit = false;
    protected $maxPageLinks = 10;

    // used by iterator interface
    protected $results = null;
    protected $resultsCounter = 0;

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
     * Returns an array of results on the given page.
     *
     * @return array
     */
    abstract public function getResults();

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
     * @param integer $nb_links The maximum number of page numbers to return
     *
     * @return array
     */
    public function getLinks($nb_links = null)
    {
        if ($nb_links == null) {
            $nb_links = $this->getMaxPageLinks();
        }
        $links = [];
        $tmp   = $this->page - floor($nb_links / 2);
        $check = $this->lastPage - $nb_links + 1;

        $limit = 1;
        if ($check > 0) {
            $limit = $check;
        }

        $begin = 1;
        if ($tmp > 0) {
            $begin = $tmp > $limit ? $limit : $tmp;
        }

        $i = (int)$begin;
        while ($i < $begin + $nb_links && $i <= $this->lastPage) {
            $links[] = $i++;
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
     * Returns the current cursor.
     *
     * @return integer
     */
    public function getCursor()
    {
        return $this->cursor;
    }

    /**
     * Sets the current cursor.
     *
     * @param integer $pos
     */
    public function setCursor($pos)
    {
        if ($pos < 1) {
            $this->cursor = 1;
        } else {
            if ($pos > $this->nbResults) {
                $this->cursor = $this->nbResults;
            } else {
                $this->cursor = $pos;
            }
        }
    }

    /**
     * Returns an object by cursor position.
     *
     * @param integer $pos
     *
     * @return mixed
     */
    public function getObjectByCursor($pos)
    {
        $this->setCursor($pos);

        return $this->getCurrent();
    }

    /**
     * Returns the current object.
     *
     * @return mixed
     */
    public function getCurrent()
    {
        return $this->retrieveObject($this->cursor);
    }

    /**
     * Returns the next object.
     *
     * @return mixed|null
     */
    public function getNext()
    {
        if (!$this->cursor + 1 > $this->nbResults) {
            return $this->retrieveObject($this->cursor + 1);
        }

        return null;
    }

    /**
     * Returns the previous object.
     *
     * @return mixed|null
     */
    public function getPrevious()
    {
        if ($this->cursor - 1 >= 1) {
            $this->retrieveObject($this->cursor - 1);
        }

        return null;
    }

    /**
     * Returns the first index on the current page.
     *
     * @return integer
     */
    public function getFirstIndex()
    {
        if ($this->page == 0) {
            return 1;
        } else {
            return ($this->page - 1) * $this->maxPerPage + 1;
        }
    }

    /**
     * Returns the last index on the current page.
     *
     * @return integer
     */
    public function getLastIndex()
    {
        if ($this->page == 0) {
            return $this->nbResults;
        } else {
            if ($this->page * $this->maxPerPage >= $this->nbResults) {
                return $this->nbResults;
            } else {
                return $this->page * $this->maxPerPage;
            }
        }
    }

    /**
     * Returns the number of results.
     *
     * @return integer
     */
    public function getNbResults()
    {
        return (int)$this->nbResults;
    }

    /**
     * Sets the number of results.
     *
     * @param integer $nb
     */
    protected function setNbResults($nb)
    {
        $this->nbResults = $nb;
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
            // set first page, which depends on a maximum set
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
     * Returns true if the properties used for iteration have been initialized.
     *
     * @return boolean
     */
    protected function isIteratorInitialized()
    {
        return null !== $this->results;
    }

    /**
     * Loads data into properties used for iteration.
     */
    protected function initializeIterator()
    {
        $this->results        = $this->getResults();
        $this->resultsCounter = count($this->results);
    }

    /**
     * Initialize iterator if not initialized yet
     */
    protected function initializeIteratorIfNotInitialized()
    {
        if (!$this->isIteratorInitialized()) {
            $this->initializeIterator();
        }
    }

    /**
     * Empties properties used for iteration.
     */
    protected function resetIterator()
    {
        $this->results        = null;
        $this->resultsCounter = 0;
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        $this->initializeIteratorIfNotInitialized();

        return current($this->results);
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        $this->initializeIteratorIfNotInitialized();

        return key($this->results);
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        $this->initializeIteratorIfNotInitialized();

        --$this->resultsCounter;

        return next($this->results);
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        $this->initializeIteratorIfNotInitialized();

        $this->resultsCounter = count($this->results);

        return reset($this->results);
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        $this->initializeIteratorIfNotInitialized();

        return $this->resultsCounter > 0;
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

    /**
     * Retrieve the object for a certain offset
     *
     * @param integer $offset
     *
     * @return object
     */
    abstract protected function retrieveObject($offset);
}
