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

    /** @var array */
    protected $changes;

    /**
     * @param Proposition $proposition
     * @param array       $changes
     */
    public function __construct(Proposition $proposition, array $changes = null)
    {
        $this->proposition = $proposition;
        $this->changes = $changes;
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

    /**
     * Get the submitted changes
     *
     * @return array
     */
    public function getChanges()
    {
        return $this->changes;
    }
}
