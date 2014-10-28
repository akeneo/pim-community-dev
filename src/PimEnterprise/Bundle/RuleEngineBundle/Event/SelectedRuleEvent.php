<?php

namespace PimEnterprise\Bundle\RuleEngineBundle\Event;

use PimEnterprise\Bundle\RuleEngineBundle\Model\RuleInterface;
use Symfony\Component\EventDispatcher\Event;
use PimEnterprise\Bundle\RuleEngineBundle\Model\RuleSubjectSetInterface;

/**
 * Selected rule event
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
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
