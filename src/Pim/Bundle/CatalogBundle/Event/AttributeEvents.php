<?php

namespace Pim\Bundle\CatalogBundle\Event;

/**
 * Catalog attribute events
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class AttributeEvents
{
    /**
     * This event is thrown before an attribute is removed.
     *
     * The event listener receives an
     * Symfony\Component\EventDispatcher\GenericEvent instance.
     *
     * @staticvar string
     */
    const PRE_REMOVE = 'pim_catalog.pre_remove.attribute';

    /**
     * This event is thrown after an attribute is removed.
     *
     * The event listener receives an
     * Symfony\Component\EventDispatcher\GenericEvent instance.
     *
     * @staticvar string
     */
    const POST_REMOVE = 'pim_catalog.post_remove.attribute';
}
