<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\RuleEngineBundle\Applier;

use PimEnterprise\Bundle\RuleEngineBundle\Model\RuleInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Engine\ApplierInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Model\RuleSubjectSetInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Model\QBRuleSubjectSetInterface;

/**
 * Set value applier.
 *
 * @author Julien Sanchez <julien@akeneo.com>
 */
class SetValueApplier implements ApplierInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports(RuleInterface $rule, RuleSubjectSetInterface $subjectSet, array $context = [])
    {
        return $rule->getType() === 'product' &&
            $rule instanceof LoadedRuleDecoratorInterface &&
            $subjectSet instanceof QBRuleSubjectSetInterface;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(RuleInterface $rule, RuleSubjectSetInterface $subjectSet, array $context = [])
    {
        $actions = $rule->getActions();

        foreach ($actions as $action) {

        }
    }
}
