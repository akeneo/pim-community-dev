<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Proposition;

use Symfony\Component\EventDispatcher\Event;
use PimEnterprise\Bundle\WorkflowBundle\Model\Proposition;

/**
 * Proposition event
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class PropositionEvent extends Event
{
    /** @var Proposition */
    protected $proposition;

    /**
     * @param Proposition $proposition
     */
    public function __construct(Proposition $proposition)
    {
        $this->proposition = $proposition;
    }

    /**
     * Set proposition
     *
     * @param Proposition $proposition
     */
    public function setProposition(array $proposition)
    {
        $this->proposition = $proposition;
    }

    /**
     * Get the proposition
     *
     * @return Proposition
     */
    public function getProposition()
    {
        return $this->proposition;
    }
}
