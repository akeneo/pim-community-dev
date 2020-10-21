<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Message;

use Akeneo\Platform\Component\EventQueue\BusinessEvent;

/**
 * @copyright 202O Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductModelRemoved extends BusinessEvent
{
    public function name(): string
    {
        return 'product_model.removed';
    }
}
