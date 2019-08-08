<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\ProductModel\Query;

use Akeneo\Pim\Enrichment\Component\Product\Factory\WriteValueCollectionFactory;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyVariantInterface;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GetValuesOfSiblings
{
    /**
     * Returns the values of the siblings of an EntityWithVariantInterface, indexed by identifier
     *
     * @return WriteValueCollectionFactory[]
     */
    public function for(EntityWithFamilyVariantInterface $entity): array;
}
