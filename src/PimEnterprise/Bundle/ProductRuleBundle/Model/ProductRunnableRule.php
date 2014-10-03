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

use PimEnterprise\Bundle\RuleEngineBundle\Model\RunnableRuleInterface;

class ProductRunnableRule implements ProductRunnableRuleInterface
{
    protected $code;

    /** @var string */
    protected $expression;

    /** @var array */
    protected $context;

    /** @var ConditionInterface[] */
    protected $conditions;

    /** @var ActionInterface[] */
    protected $actions;

    public function getExpression()
    {
        return $this->expression;
    }

    public function setExpression($expression)
    {
        $this->expression = $expression;

        return $this;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->getCode();
    }

    /**
     * @param string $code
     *
     * @return RunnableRuleInterface
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }


}
