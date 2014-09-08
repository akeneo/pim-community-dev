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
 * Published product events
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
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

    /**
     * This event is dispatched before a product is unpublished
     *
     * The event listener receives an
     * PimEnterprise\Bundle\WorkflowBundle\Event\PublishedProductEvent instance
     *
     * @staticvar string
     */
    const PRE_UNPUBLISH = 'pimee_workflow.published_product.pre_unpublish';

    /**
     * This event is dispatched after a product has just been unpublished
     *
     * The event listener receives an
     * PimEnterprise\Bundle\WorkflowBundle\Event\PublishedProductEvent instance
     *
     * @staticvar string
     */
    const POST_UNPUBLISH = 'pimee_workflow.published_product.post_unpublish';
}
