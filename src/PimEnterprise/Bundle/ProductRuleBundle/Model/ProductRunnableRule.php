<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ProductRuleBundle\Model;

use Pim\Bundle\CatalogBundle\Doctrine\Query\ProductQueryBuilderInterface;

class ProductRunnableRule implements ProductRunnableRuleInterface
{
    /** @var string */
    protected $code;

    /** @var ProductQueryBuilderInterface */
    protected $qb;

    /** @var array */
    protected $context;

    /** @var ConditionInterface[] */
    protected $conditions;

    /** @var ActionInterface[] */
    protected $actions;

    /**
     * {@inheritdoc}
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * {@inheritdoc}
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getQueryBuilder()
    {
        return $this->qb;
    }

    /**
     * {@inheritdoc}
     */
    public function setQueryBuilder(ProductQueryBuilderInterface $pqb)
    {
        $this->qb = $pqb;

        return $this;
    }
}
