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

use PimEnterprise\Bundle\RuleEngineBundle\Model\RuleInterface;

class ChainedRunner implements RunnerInterface
{
    /** RunnerInterface[] ordered runner with priority */
    protected $runners;

    public function registerRunner(RunnerInterface $runner)
    {
        $this->runners[]= $runner;
    }

    public function supports(RuleInterface $rule)
    {
        return true;
    }

    public function run(RuleInterface $rule)
    {
        foreach ($runners as $runner) {
            if ($runner->supports($rule)) {
                return $runner->run($rule);
            }
        }

        throw new \LogicException('No runner available');
    }
}
