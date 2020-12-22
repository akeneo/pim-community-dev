<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\EntityWithFamilyVariant;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Symfony\Component\Security\Core\Exception\InvalidArgumentException;

/**
 * Detaches a variant product from its parent, turning it into a simple product
 *
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface RemoveParentInterface
{
    /**
     * @param ProductInterface $product
     *
     * @throws InvalidArgumentException
     */
    public function from(ProductInterface $product): void;
}
