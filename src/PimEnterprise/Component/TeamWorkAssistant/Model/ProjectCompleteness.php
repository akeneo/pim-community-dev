<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\TeamWorkAssistant\Model;

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

        if (0 === $productCount = $this->productCountDone + $this->productCountInProgress + $this->productCountTodo) {
            $productCount = 1; // Avoid division by 0
        }

        $this->productCount = $productCount;
    }

    /**
     * Returns the number of products for to do.
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
     * Returns the project completeness in percent for to do.
     *
     * @return int
     */
    public function getRatioForTodo()
    {
        return round($this->productCountTodo / $this->productCount * 100, 2);
    }

    /**
     * Returns the project completeness in percent for in progress.
     *
     * @return int
     */
    public function getRatioForInProgress()
    {
        return round($this->productCountInProgress / $this->productCount * 100, 2);
    }

    /**
     * Returns the project completeness in percent for done.
     *
     * @return int
     */
    public function getRatioForDone()
    {
        return round($this->productCountDone / $this->productCount * 100, 2);
    }

    /**
     * Returns if the project is complete or not.
     *
     * @return bool
     */
    public function isComplete()
    {
        return $this->productCountDone === $this->productCount;
    }
}
