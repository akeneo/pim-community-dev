<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Proposition;

/**
 * Proposition events
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class PropositionEvents
{
    /**
     * This event is dispatched before proposition is flushed to database
     *
     * The event listener receives an
     * PimEnterprise\Bundle\WorkflowBundle\Proposition\PropositionEvent instance
     *
     * @staticvar string
     */
    const PRE_UPDATE = 'pimee_workflow.proposition.pre_update';

    /**
     * This event is dispatched before proposition is applied a product
     *
     * The event listener receives an
     * PimEnterprise\Bundle\WorkflowBundle\Proposition\PropositionEvent instance
     *
     * @staticvar string
     */
    const PRE_APPLY = 'pimee_workflow.proposition.pre_apply';
}
