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

use PimEnterprise\Bundle\RuleEngineBundle\Model\RuleDefinitionInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Model\RuleSubjectSetInterface;

/**
 * Selected rule event
 *
 * @author Julien Sanchez <julien@akeneo.com>
 */
class SelectedRuleEvent extends RuleEvent
{
    /** @var RuleSubjectSetInterface */
    protected $subjectSet;

    /**
     * @param RuleDefinitionInterface $definition
     * @param RuleSubjectSetInterface $subjectSet
     */
    public function __construct(RuleDefinitionInterface $definition, RuleSubjectSetInterface $subjectSet)
    {
        parent::__construct($definition);

        $this->subjectSet = $subjectSet;
    }

    /**
     * @return RuleSubjectSetInterface
     */
    public function getSubjectSet()
    {
        return $this->subjectSet;
    }
}
