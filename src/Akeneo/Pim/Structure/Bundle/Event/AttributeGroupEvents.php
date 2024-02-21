<?php

namespace Akeneo\Pim\Structure\Bundle\Event;

/**
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class AttributeGroupEvents
{
    /**
     * This event is dispatched after an attribute group is created or updated by the UI.
     */
    const POST_SAVE = 'pim_enrich.attribute_group.post_save';
}
