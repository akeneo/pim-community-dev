<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\RuleEngineBundle\Runner;

use PimEnterprise\Bundle\RuleEngineBundle\Model\RunnableRuleInterface;

/**
 * Executes a business instance
 */
interface RunnerInterface
{
    /**
     * @param RunnableRuleInterface $rule

     *
*@return mixed
     */
    public function run(RunnableRuleInterface $rule);

    /**
     * @param RunnableRuleInterface $rule

     *
*@return bool
     */
    public function supports(RunnableRuleInterface $rule);
}
