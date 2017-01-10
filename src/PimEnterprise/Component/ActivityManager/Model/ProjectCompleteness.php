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
class ProjectCompleteness
{
    /** @var int */
    protected $productCount;

    /** @var int */
    protected $productCountTodo;

    /** @var int */
    protected $productCountInProgress;

    /** @var int */
    protected $productCountDone;

    /**
     * @param int $todo
     * @param int $inProgress
     * @param int $done
     */
    public function __construct($todo, $inProgress, $done)
    {
        $this->productCountTodo = (int) $todo;
        $this->productCountInProgress = (int) $inProgress;
        $this->productCountDone = (int) $done;

        $this->productCount = $this->productCountDone + $this->productCountInProgress + $this->productCountTodo;
    }

    /**
     * Returns the number of products for todo.
     *
     * @return int
     */
    public function getProductsCountTodo()
    {
        return $this->productCountTodo;
    }

    /**
     * Returns the number of products for in progress.
     *
     * @return int
     */
    public function getProductsCountInProgress()
    {
        return $this->productCountInProgress;
    }

    /**
     * Returns the number of products for Done.
     *
     * @return int
     */
    public function getProductsCountDone()
    {
        return $this->productCountDone;
    }

    /**
     * Returns the project completeness in percent for todo.
     *
     * @return int
     */
    public function getRatioForTodo()
    {
        return (int) ($this->productCountTodo / $this->productCount * 100);
    }

    /**
     * Returns the project completeness in percent for in progress.
     *
     * @return int
     */
    public function getRatioForInProgress()
    {
        return (int) ($this->productCountInProgress / $this->productCount * 100);
    }

    /**
     * Returns the project completeness in percent for done.
     *
     * @return int
     */
    public function getRatioForDone()
    {
        return (int) ($this->productCountDone / $this->productCount * 100);
    }

    /**
     * Returns if the project is complete or not.
     *
     * @return bool
     */
    public function isComplete()
    {
        return 99 < $this->getRatioForDone();
    }
}
