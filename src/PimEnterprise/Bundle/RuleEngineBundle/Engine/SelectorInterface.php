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

use PimEnterprise\Bundle\RuleEngineBundle\Model\RuleInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Model\RuleSubjectSetInterface;

/**
 * Selects subjects impacted by a rule.
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
interface SelectorInterface
{
    /**
     * @param RuleInterface $instance
     *
     * @return RuleSubjectSetInterface
     */
    public function select(RuleInterface $instance, array $context = []);

    /**
     * @param RuleInterface $instance
     *
     * @return bool
     */
    public function supports(RuleInterface $instance, array $context = []);
}
