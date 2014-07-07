<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Event;

/**
 * Published product events
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class PublishedProductEvents
{
    /**
     * This event is dispatched before a product is published
     *
     * The event listener receives an
     * PimEnterprise\Bundle\WorkflowBundle\Event\PublishedProductEvent instance
     *
     * @staticvar string
     */
    const PRE_PUBLISH = 'pimee_workflow.published_product.pre_publish';

    /**
     * This event is dispatched after a product has just been published
     *
     * The event listener receives an
     * PimEnterprise\Bundle\WorkflowBundle\Event\PublishedProductEvent instance
     *
     * @staticvar string
     */
    const POST_PUBLISH = 'pimee_workflow.published_product.post_publish';
}
