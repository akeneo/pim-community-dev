<?php

namespace Oro\Bundle\DataGridBundle\Extension\Pager;

interface PagerInterface
{
    /**
     * Initialize the pager.
     *
     * @return void
     */
    public function init();

    /**
     * Set max records per page
     *
     * @param  int $maxPerPage
     *
     * @return void
     */
    public function setMaxPerPage($maxPerPage);

    /**
     * Get max records per page
     *
     * @return int
     */
    public function getMaxPerPage();

    /**
     * Set current page
     *
     * @param  int $page
     *
     * @return void
     */
    public function setPage($page);

    /**
     * Get current page
     *
     * @return int
     */
    public function getPage();

    /**
     * Returns the previous page.
     *
     * @return int
     */
    public function getPreviousPage();

    /**
     * Returns the next page.
     *
     * @return integer
     */
    public function getNextPage();

    /**
     * Returns the last page number.
     *
     * @return integer
     */
    public function getLastPage();

    /**
     * Returns the first page number.
     *
     * @return integer
     */
    public function getFirstPage();

    /**
     * Returns an array of page numbers to use in pagination links.
     *
     * @param  integer $nbLinks The maximum number of page numbers to return
     *
     * @return array
     */
    public function getLinks($nbLinks = null);

    /**
     * @return boolean
     */
    public function haveToPaginate();

    /**
     * Returns the number of results.
     *
     * @return integer
     */
    public function getNbResults();
}
