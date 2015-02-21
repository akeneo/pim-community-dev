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
 * ProductDraft events
 *
 * @author Gildas Quemener <gildas@akeneo.com>
 */
class ProductDraftEvents
{
    /**
     * This event is dispatched before product draft is flushed to database
     *
     * The event listener receives an
     * PimEnterprise\Bundle\WorkflowBundle\ProductDraft\ProductDraftEvent instance
     *
     * @staticvar string
     */
    const PRE_UPDATE = 'pimee_workflow.product_draft.pre_update';

    /**
     * This event is dispatched before product draft is applied a product
     *
     * The event listener receives an
     * PimEnterprise\Bundle\WorkflowBundle\ProductDraft\ProductDraftEvent instance
     *
     * @staticvar string
     */
    const PRE_APPLY = 'pimee_workflow.product_draft.pre_apply';

    /**
     * This event is dispatched before product draft is approved
     *
     * The event listener receives an
     * PimEnterprise\Bundle\WorkflowBundle\ProductDraft\ProductDraftEvent instance
     *
     * @staticvar string
     */
    const PRE_APPROVE = 'pimee_workflow.product_draft.pre_approve';

    /**
     * This event is dispatched before product draft is refused
     *
     * The event listener receives an
     * PimEnterprise\Bundle\WorkflowBundle\ProductDraft\ProductDraftEvent instance
     *
     * @staticvar string
     */
    const PRE_REFUSE = 'pimee_workflow.product_draft.pre_refuse';

    /**
     * This event is dispatched before product draft is marked as ready
     *
     * The event listener receives an
     * PimEnterprise\Bundle\WorkflowBundle\ProductDraft\ProductDraftEvent instance
     *
     * @staticvar string
     */
    const PRE_READY = 'pimee_workflow.product_draft.pre_ready';
}
