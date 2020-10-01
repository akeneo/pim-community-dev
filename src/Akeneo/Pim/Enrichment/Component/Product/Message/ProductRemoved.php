<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Message;

use Akeneo\Platform\Component\EventQueue\BusinessEvent;

/**
 * Business event triggered when a product is created.
 * The `data` property contains a product normalized to the standard format.
 *
 * @copyright 202O Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductRemoved extends BusinessEvent
{
    public function name(): string
    {
        return 'product.removed';
    }
}
