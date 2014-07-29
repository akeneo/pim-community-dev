<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Event;

/**
 * Change set events
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class ChangeSetEvents
{
    /**
     * This event is dispatched before computed value change is stored
     *
     * The event listener receives an
     * PimEnterprise\Bundle\WorkflowBundle\Event\ChangeSetEvent instance
     *
     * @staticvar string
     */
    const PREPARE_CHANGE = 'pimee_workflow.change_set.prepare_change';
}
