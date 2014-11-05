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

use PimEnterprise\Bundle\RuleEngineBundle\Model\LoadedRuleInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Model\RuleSubjectSetInterface;

/**
 * Applies a rule.
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
interface ApplierInterface
{
    /**
     * @param LoadedRuleInterface     $rule
     * @param RuleSubjectSetInterface $subjectSet
     *
     * @return mixed
     */
    public function apply(LoadedRuleInterface $rule, RuleSubjectSetInterface $subjectSet);

    /**
     * @param LoadedRuleInterface     $rule
     * @param RuleSubjectSetInterface $subjectSet
     *
     * @return bool
     */
    public function supports(LoadedRuleInterface $rule, RuleSubjectSetInterface $subjectSet);
}
