<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Component\Event;

/**
 * EntityWithValuesDraftEvents events
 *
 * @author Gildas Quemener <gildas@akeneo.com>
 */
class EntityWithValuesDraftEvents
{
    /**
     * This event is dispatched before draft is applied on an entity with values
     *
     * The event listener receives a Symfony\Component\EventDispatcher\GenericEvent instance
     */
    const PRE_APPLY = 'pimee_workflow.draft.pre_apply';

    /**
     * This event is dispatched after draft is applied on an entity with values
     *
     * The event listener receives a Symfony\Component\EventDispatcher\GenericEvent instance
     */
    const POST_APPLY = 'pimee_workflow.draft.post_apply';

    /**
     * This event is dispatched before draft is approved
     *
     * The event listener receives a Symfony\Component\EventDispatcher\GenericEvent instance
     */
    const PRE_APPROVE = 'pimee_workflow.draft.pre_approve';

    /**
     * This event is dispatched after draft is approved
     *
     * The event listener receives a Symfony\Component\EventDispatcher\GenericEvent instance
     */
    const POST_APPROVE = 'pimee_workflow.draft.post_approve';

    /**
     * This event is dispatched before draft is refused
     *
     * The event listener receives a Symfony\Component\EventDispatcher\GenericEvent instance
     */
    const PRE_REFUSE = 'pimee_workflow.draft.pre_refuse';

    /**
     * This event is dispatched after draft is refused
     *
     * The event listener receives a Symfony\Component\EventDispatcher\GenericEvent instance
     */
    const POST_REFUSE = 'pimee_workflow.draft.post_refuse';

    /**
     * This event is dispatched before draft is partially approved
     *
     * The event listener receives a Symfony\Component\EventDispatcher\GenericEvent instance
     */
    const PRE_PARTIAL_APPROVE = 'pimee_workflow.draft.pre_partial_approve';

    /**
     * This event is dispatched after draft is partially approved
     *
     * The event listener receives a Symfony\Component\EventDispatcher\GenericEvent instance
     */
    const POST_PARTIAL_APPROVE = 'pimee_workflow.draft.post_partial_approve';

    /**
     * This event is dispatched before draft is partially refused
     *
     * The event listener receives a Symfony\Component\EventDispatcher\GenericEvent instance
     */
    const PRE_PARTIAL_REFUSE = 'pimee_workflow.draft.pre_partial_refuse';

    /**
     * This event is dispatched after draft is partially refused
     *
     * The event listener receives a Symfony\Component\EventDispatcher\GenericEvent instance
     */
    const POST_PARTIAL_REFUSE = 'pimee_workflow.draft.post_partial_refuse';

    /**
     * This event is dispatched before draft is removed
     *
     * The event listener receives a Symfony\Component\EventDispatcher\GenericEvent instance
     */
    const PRE_REMOVE = 'pimee_workflow.draft.pre_remove';

    /**
     * This event is dispatched after draft is removed
     *
     * The event listener receives a Symfony\Component\EventDispatcher\GenericEvent instance
     */
    const POST_REMOVE = 'pimee_workflow.draft.post_remove';

    /**
     * This event is dispatched before draft is marked as ready
     *
     * The event listener receives a Symfony\Component\EventDispatcher\GenericEvent instance
     */
    const PRE_READY = 'pimee_workflow.draft.pre_ready';

    /**
     * This event is dispatched after draft is marked as ready
     *
     * The event listener receives a Symfony\Component\EventDispatcher\GenericEvent instance
     */
    const POST_READY = 'pimee_workflow.draft.post_ready';
}
