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

namespace Akeneo\Pim\Enrichment\Product\Application\Applier;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetCategories;
use Akeneo\Pim\Enrichment\Product\Domain\Model\ProductIdentifier;
use Akeneo\Pim\Enrichment\Product\Domain\Query\GetNonViewableCategoryCodes;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;

class SetCategoriesApplier
{
    public function __construct(
        private ObjectUpdaterInterface $productUpdater,
        private GetNonViewableCategoryCodes $getNonViewableCategories
    ) {
    }

    public function apply(ProductInterface $product, SetCategories $setCategories, int $userId): void
    {
        $productIdentifier = ProductIdentifier::fromString($product->getIdentifier());

        $nonViewableCategories = $this->getNonViewableCategories->fromProductIdentifiers([$productIdentifier], $userId);
        $nonViewableCategoriesForProduct = $nonViewableCategories[$productIdentifier->asString()] ?? [];

        $this->productUpdater->update($product, [
            'categories' => \array_merge($setCategories->categoryCodes(), $nonViewableCategoriesForProduct),
        ]);
    }
}
