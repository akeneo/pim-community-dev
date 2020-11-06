<?php

namespace Oro\Bundle\DataGridBundle\Extension\Pager;

interface PagerInterface
{
    /**
     * Initialize the pager.
     */
    public function init(): void;

    /**
     * Set max records per page
     *
     * @param  int $maxPerPage
     */
    public function setMaxPerPage(int $maxPerPage): void;

    /**
     * Get max records per page
     */
    public function getMaxPerPage(): int;

    /**
     * Set current page
     *
     * @param  int $page
     */
    public function setPage(int $page): void;

    /**
     * Get current page
     */
    public function getPage(): int;

    /**
     * Returns the number of results.
     */
    public function getNbResults(): int;
}
