<?php

namespace Pim\Bundle\EnrichBundle\Event;

/**
 * Enrich events
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class CategoryEvents
{
    /**
     * This event is dispatched each time a category is being edited.
     *
     * The event listener receives an
     * Symfony\Component\EventDispatcher\GenericEvent instance.
     *
     * @deprecated
     */
    const PRE_EDIT = 'pim_enrich.category.pre_edit';

    /**
     * This event is dispatched each time a category has been edited.
     *
     * The event listener receives an
     * Symfony\Component\EventDispatcher\GenericEvent instance.
     *
     * @deprecated
     */
    const POST_EDIT = 'pim_enrich.category.post_edit';

    /**
     * This event is dispatched each time a category is being created.
     *
     * The event listener receives an
     * Symfony\Component\EventDispatcher\GenericEvent instance.
     *
     * @deprecated
     */
    const PRE_CREATE = 'pim_enrich.category.pre_create';

    /**
     * This event is dispatched each time a category has been created.
     *
     * The event listener receives an
     * Symfony\Component\EventDispatcher\GenericEvent instance.
     *
     * @deprecated
     */
    const POST_CREATE = 'pim_enrich.category.post_create';
}
