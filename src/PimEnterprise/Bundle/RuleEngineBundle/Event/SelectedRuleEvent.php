<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\RuleEngineBundle\Event;

use PimEnterprise\Bundle\RuleEngineBundle\Model\RuleInterface;
use Symfony\Component\EventDispatcher\Event;
use PimEnterprise\Bundle\RuleEngineBundle\Model\RuleSubjectSetInterface;

/**
 * Selected rule event
 *
 * @author Julien Sanchez <julien@akeneo.com>
 */
class SelectedRuleEvent extends Event
{
    /** @var RuleInterface */
    protected $rule;

    /** @var RuleSubjectSetInterface */
    protected $subjectSet;

    public function __construct(RuleInterface $rule, RuleSubjectSetInterface $subjectSet)
    {
        $this->rule       = $rule;
        $this->subjectSet = $subjectSet;
    }

    /**
     * @return RuleInterface
     */
    public function getRule()
    {
        return $this->rule;
    }

    /**
     * @return RuleSubjectSetInterface
     */
    public function getSubjectSet()
    {
        return $this->subjectSet;
    }
}
