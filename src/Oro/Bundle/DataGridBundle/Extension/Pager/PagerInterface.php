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
     * Returns the number of results.
     *
     * @return integer
     */
    public function getNbResults();
}
