<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\RuleEngineBundle\Engine;

use PimEnterprise\Bundle\RuleEngineBundle\Model\LoadedRuleDecoratorInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Model\RuleInterface;

/**
 * Loads a rule to make to be able to apply it.
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
interface LoaderInterface
{
    /**
     * @param RuleInterface $rule
     *
     * @return LoadedRuleDecoratorInterface
     */
    public function load(RuleInterface $rule);

    /**
     * @param RuleInterface $rule
     *
     * @return bool
     */
    public function supports(RuleInterface $rule);
}
