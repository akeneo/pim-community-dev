<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Bundle\Event;

final class AttributeEvents
{
    /**
     * This event is dispatched after attribute values are cleaned up from products & product models.
     */
    const POST_CLEAN = 'pim_enrich.attribute.post_clean';
}
