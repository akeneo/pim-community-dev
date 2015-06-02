<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Bundle\RuleEngineBundle\Event;

use Akeneo\Bundle\RuleEngineBundle\Model\RuleDefinitionInterface;

/**
 * Class SkippedSubjectRuleEvent
 *
 * @author    Stephane Chapeau <stephane.chapeau@akeneo.com>
 */
class SkippedSubjectRuleEvent extends RuleEvent
{
    /** @var mixed */
    protected $subject;

    /** @var  array */
    protected $reasons;

    /**
     * @param RuleDefinitionInterface $definition
     * @param mixed                   $subject
     * @param array                   $reasons
     */
    public function __construct(RuleDefinitionInterface $definition, $subject, array $reasons)
    {
        parent::__construct($definition);
        $this->subject = $subject;
        $this->reasons = $reasons;
    }

    /**
     * @return mixed
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @return array
     */
    public function getReasons()
    {
        return $this->reasons;
    }
}
