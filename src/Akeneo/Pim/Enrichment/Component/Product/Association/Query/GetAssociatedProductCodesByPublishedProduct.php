<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Association\Query;

use Akeneo\Pim\Enrichment\Component\Product\Model\AssociationInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GetAssociatedProductCodesByPublishedProduct
{
    /**
     * Return codes of associated products
     *
     * @return string[]
     */
    public function getCodes(int $publishedProductId, AssociationInterface $association): array;
}
