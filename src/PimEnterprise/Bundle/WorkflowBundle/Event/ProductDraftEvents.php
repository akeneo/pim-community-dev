<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Event;

/**
 * Proposition events
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class ProductDraftEvents
{
    /**
     * This event is dispatched before proposition is flushed to database
     *
     * The event listener receives an
     * PimEnterprise\Bundle\WorkflowBundle\ProductDraft\ProductDraftEvent instance
     *
     * @staticvar string
     */
    const PRE_UPDATE = 'pimee_workflow.proposition.pre_update';

    /**
     * This event is dispatched before proposition is applied a product
     *
     * The event listener receives an
     * PimEnterprise\Bundle\WorkflowBundle\ProductDraft\ProductDraftEvent instance
     *
     * @staticvar string
     */
    const PRE_APPLY = 'pimee_workflow.proposition.pre_apply';

    /**
     * This event is dispatched before proposition is approved
     *
     * The event listener receives an
     * PimEnterprise\Bundle\WorkflowBundle\ProductDraft\ProductDraftEvent instance
     *
     * @staticvar string
     */
    const PRE_APPROVE = 'pimee_workflow.proposition.pre_approve';

    /**
     * This event is dispatched before proposition is refused
     *
     * The event listener receives an
     * PimEnterprise\Bundle\WorkflowBundle\ProductDraft\ProductDraftEvent instance
     *
     * @staticvar string
     */
    const PRE_REFUSE = 'pimee_workflow.proposition.pre_refuse';

    /**
     * This event is dispatched before proposition is marked as ready
     *
     * The event listener receives an
     * PimEnterprise\Bundle\WorkflowBundle\ProductDraft\ProductDraftEvent instance
     *
     * @staticvar string
     */
    const PRE_READY = 'pimee_workflow.proposition.pre_ready';
}
