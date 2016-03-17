<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\Workflow\Event;

/**
 * ProductDraft events
 *
 * @author Gildas Quemener <gildas@akeneo.com>
 */
class ProductDraftEvents
{
    /**
     * This event is dispatched before product draft is applied a product
     *
     * The event listener receives a Symfony\Component\EventDispatcher\GenericEvent instance
     */
    const PRE_APPLY = 'pimee_workflow.product_draft.pre_apply';

    /**
     * This event is dispatched after product draft is applied a product
     *
     * The event listener receives a Symfony\Component\EventDispatcher\GenericEvent instance
     */
    const POST_APPLY = 'pimee_workflow.product_draft.post_apply';

    /**
     * This event is dispatched before product draft is approved
     *
     * The event listener receives a Symfony\Component\EventDispatcher\GenericEvent instance
     */
    const PRE_APPROVE = 'pimee_workflow.product_draft.pre_approve';

    /**
     * This event is dispatched after product draft is approved
     *
     * The event listener receives a Symfony\Component\EventDispatcher\GenericEvent instance
     */
    const POST_APPROVE = 'pimee_workflow.product_draft.post_approve';

    /**
     * This event is dispatched before product draft is refused
     *
     * The event listener receives a Symfony\Component\EventDispatcher\GenericEvent instance
     */
    const PRE_REFUSE = 'pimee_workflow.product_draft.pre_refuse';

    /**
     * This event is dispatched after product draft is refused
     *
     * The event listener receives a Symfony\Component\EventDispatcher\GenericEvent instance
     */
    const POST_REFUSE = 'pimee_workflow.product_draft.post_refuse';

    /**
     * This event is dispatched before product draft is partially approved
     *
     * The event listener receives a Symfony\Component\EventDispatcher\GenericEvent instance
     */
    const PRE_PARTIAL_APPROVE = 'pimee_workflow.product_draft.pre_partial_approve';

    /**
     * This event is dispatched after product draft is partially approved
     *
     * The event listener receives a Symfony\Component\EventDispatcher\GenericEvent instance
     */
    const POST_PARTIAL_APPROVE = 'pimee_workflow.product_draft.post_partial_approve';

    /**
     * This event is dispatched before product draft is partially refused
     *
     * The event listener receives a Symfony\Component\EventDispatcher\GenericEvent instance
     */
    const PRE_PARTIAL_REFUSE = 'pimee_workflow.product_draft.pre_partial_refuse';

    /**
     * This event is dispatched after product draft is partially refused
     *
     * The event listener receives a Symfony\Component\EventDispatcher\GenericEvent instance
     */
    const POST_PARTIAL_REFUSE = 'pimee_workflow.product_draft.post_partial_refuse';

    /**
     * This event is dispatched before product draft is removed
     *
     * The event listener receives a Symfony\Component\EventDispatcher\GenericEvent instance
     */
    const PRE_REMOVE = 'pimee_workflow.product_draft.pre_remove';

    /**
     * This event is dispatched after product draft is removed
     *
     * The event listener receives a Symfony\Component\EventDispatcher\GenericEvent instance
     */
    const POST_REMOVE = 'pimee_workflow.product_draft.post_remove';

    /**
     * This event is dispatched before product draft is marked as ready
     *
     * The event listener receives a Symfony\Component\EventDispatcher\GenericEvent instance
     */
    const PRE_READY = 'pimee_workflow.product_draft.pre_ready';

    /**
     * This event is dispatched after product draft is marked as ready
     *
     * The event listener receives a Symfony\Component\EventDispatcher\GenericEvent instance
     */
    const POST_READY = 'pimee_workflow.product_draft.post_ready';
}
