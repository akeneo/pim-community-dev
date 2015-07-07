<?php

namespace Pim\Bundle\CatalogBundle\Event;

/**
 * Catalog attribute option events
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class AttributeOptionEvents
{
    /**
     * This event is thrown before an attribute option is removed.
     *
     * The event listener receives an
     * Symfony\Component\EventDispatcher\GenericEvent instance.
     *
     * @staticvar string
     */
    const PRE_REMOVE = 'pim_catalog.pre_remove.attribute_option';
}
