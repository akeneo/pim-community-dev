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

/**
 * Executes a business instance
 */
interface RunnerInterface
{
    /**
     * @param RuleInterface $rule
     *
     * @return mixed
     */
    public function run(RuleInterface $rule);

    /**
     * @param RuleInterface $rule
     *
     * @return bool
     */
    public function supports(RuleInterface $rule);
}
