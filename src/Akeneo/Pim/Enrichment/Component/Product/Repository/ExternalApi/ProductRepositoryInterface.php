<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Repository\ExternalApi;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Ramsey\Uuid\UuidInterface;

/**
 * Repository interface for product resources
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ProductRepositoryInterface extends IdentifiableObjectRepositoryInterface
{
    /**
     * Find a product from it's Uuid
     */
    public function findOneByUuid(UuidInterface $uuid): ?ProductInterface;
}
