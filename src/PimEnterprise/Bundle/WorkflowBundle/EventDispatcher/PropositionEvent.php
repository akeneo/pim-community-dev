<?php

namespace PimEnterprise\Bundle\WorkflowBundle\EventDispatcher;

use Symfony\Component\EventDispatcher\Event;

/**
 * Proposition event
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class PropositionEvent extends Event
{
    /** @staticvar string */
    const BEFORE_APPLY_CHANGES = 'pimee_workflow.proposition.before_apply_changes';

    /** @var array */
    protected $changes;

    /**
     * @param array $changes
     */
    public function __construct(array $changes)
    {
        $this->setChanges($changes);
    }

    /**
     * Set changes
     *
     * @param array $changes
     */
    public function setChanges(array $changes)
    {
        $this->changes = $changes;
    }

    /**
     * Get the changes
     *
     * @return array
     */
    public function getChanges()
    {
        return $this->changes;
    }
}
