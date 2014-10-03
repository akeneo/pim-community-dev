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
 * Executes or dry run a business rule
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
interface DryRunnerInterface extends RunnerInterface
{
    /**
     * @param RunnableRuleInterface $rule
     *
     * @return mixed
     */
    public function dryRun(RunnableRuleInterface $rule);
}
