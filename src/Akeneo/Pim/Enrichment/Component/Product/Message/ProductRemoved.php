<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Message;

use Akeneo\Platform\Component\EventQueue\Event;

/**
 * Business event triggered when a product is removed.
 * The `data` property contains a product normalized to the standard format.
 *
 * @copyright 202O Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductRemoved extends Event
{
    public function getName(): string
    {
        return 'product.removed';
    }
}
