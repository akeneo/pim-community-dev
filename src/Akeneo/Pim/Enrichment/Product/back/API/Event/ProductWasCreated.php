<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\API\Event;

use Ramsey\Uuid\UuidInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductWasCreated
{
    public function __construct(private UuidInterface $productUuid)
    {
    }

    public function productUuid(): UuidInterface
    {
        return $this->productUuid;
    }
}
