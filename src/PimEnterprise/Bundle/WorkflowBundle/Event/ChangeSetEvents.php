<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\Event;

/**
 * Change set events
 *
 * @author Gildas Quemener <gildas@akeneo.com>
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
