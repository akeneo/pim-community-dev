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

/**
 * Chained rule applier. Find the applier able to handle a rule, and apply it.
 *
 * @author Julien Sanchez <julien@akeneo.com>
 */
class ChainedApplier implements ApplierInterface
{
    /** @var ApplierInterface[] ordered applier with priority */
    protected $appliers = [];

    /**
     * @param ApplierInterface $applier
     *
     * @return ChainedApplier
     */
    public function addApplier(ApplierInterface $applier)
    {
        $this->appliers[] = $applier;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(RuleInterface $rule, RuleSubjectSetInterface $subjectSet)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(RuleInterface $rule, RuleSubjectSetInterface $subjectSet)
    {
        foreach ($this->appliers as $applier) {
            // $actions
            if ($applier->supports($rule)) {
                return $applier->run($rule);
            }
        }

        throw new \LogicException(sprintf('No applier available for the rule "%s".', $rule->getCode()));
    }
}
