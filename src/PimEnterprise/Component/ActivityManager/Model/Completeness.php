<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ActivityManager\Model;

/**
 * Value object which represent the completeness and handle the completeness numbers.
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class Completeness
{
    /** @var array */
    protected $productNumbers;

    /** @var int */
    protected $totalProducts;

    /**
     * @param array $productNumbers
     */
    public function __construct(array $productNumbers)
    {
        $this->productNumbers = array_map('intval', $productNumbers);
        $this->totalProducts = array_sum($this->productNumbers);
    }

    /**
     * Returns the number of products for todo.
     *
     * @return array
     */
    public function getProductsNumberForTodo()
    {
        return $this->productNumbers['todo'];
    }

    /**
     * Returns the number of products for in progress.
     *
     * @return array
     */
    public function getProductsNumberForInProgress()
    {
        return $this->productNumbers['in_progress'];
    }

    /**
     * Returns the number of products for Done.
     *
     * @return array
     */
    public function getProductsNumberForDone()
    {
        return $this->productNumbers['done'];
    }

    /**
     * Returns the project completeness in percent for todo.
     *
     * @return int
     */
    public function getCompletenessForTodo()
    {
        return $this->productNumbers['todo'] / $this->totalProducts * 100;
    }

    /**
     * Returns the project completeness in percent for in progress.
     *
     * @return int
     */
    public function getCompletenessForInProgress()
    {
        return $this->productNumbers['in_progress'] / $this->totalProducts * 100;
    }

    /**
     * Returns the project completeness in percent for done.
     *
     * @return int
     */
    public function getCompletenessForDone()
    {
        return $this->productNumbers['done'] / $this->totalProducts * 100;
    }

    /**
     * Returns if the project is complete or not.
     *
     * @return bool
     */
    public function isComplete()
    {
        return 99 < $this->getCompletenessForDone();
    }
}
