<?php

declare(strict_types=1);

namespace Pim\Component\Catalog\EntityWithFamilyVariant\Query;

use Pim\Component\Catalog\Model\EntityWithFamilyVariantInterface;
use Pim\Component\Catalog\Model\ValueCollectionInterface;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GetValuesOfSiblings
{
    /**
     * Returns the values of the siblings of an EntityWithVariantInterface, indexed by identifier
     *
     * @return ValueCollectionInterface[]
     */
    public function for(EntityWithFamilyVariantInterface $entity): array;
}
