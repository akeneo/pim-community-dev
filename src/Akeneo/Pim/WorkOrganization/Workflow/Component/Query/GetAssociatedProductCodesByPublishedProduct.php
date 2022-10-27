<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Component\Query;

use Akeneo\Pim\Enrichment\Component\Product\Model\AssociationInterface;

interface GetAssociatedProductCodesByPublishedProduct
{
    /**
     * @return string[]
     */
    public function getCodes(int $publishedProductId, AssociationInterface $association): array;

    /**
     * @return string[]
     */
    public function getUuids(int $publishedProductId, AssociationInterface $association): array;
}
