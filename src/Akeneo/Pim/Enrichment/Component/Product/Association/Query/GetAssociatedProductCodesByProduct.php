<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Association\Query;

use Akeneo\Pim\Enrichment\Component\Product\Model\AssociationInterface;
use Ramsey\Uuid\UuidInterface;

/**
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GetAssociatedProductCodesByProduct
{
    /**
     * Return codes of associated products
     *
     * @return string[]
     */
    public function getCodes(UuidInterface $productUuid, AssociationInterface $association): array;
}
