<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Association\Query;

use Akeneo\Pim\Enrichment\Component\Product\Model\AssociationInterface;
use Ramsey\Uuid\UuidInterface;

/**
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GetAssociatedProductUuidsByProduct
{
    /**
     * @return string[]
     */
    public function getIdentifiers(UuidInterface $productUuid, AssociationInterface $association): array;

    /**
     * @return string[]
     */
    public function getUuids(UuidInterface $productUuid, AssociationInterface $association): array;
}
