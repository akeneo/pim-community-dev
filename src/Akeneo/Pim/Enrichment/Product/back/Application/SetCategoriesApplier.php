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

namespace Akeneo\Pim\Enrichment\Product\Application;

use Akeneo\Pim\Enrichment\Category\API\Query\GetViewableCategories;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetCategories;
use Akeneo\Pim\Enrichment\Product\Domain\Model\ProductIdentifier;
use Akeneo\Pim\Enrichment\Product\Domain\Query\GetCategoryCodes;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;

class SetCategoriesApplier
{
    public function __construct(
        private ObjectUpdaterInterface $productUpdater,
        private GetCategoryCodes $getCategoryCodes,
        private GetViewableCategories $getViewableCategories
    ) {
    }

    public function apply(ProductInterface $product, SetCategories $setCategories, int $userId): void
    {
        $productIdentifier = ProductIdentifier::fromString($product->getIdentifier());

        $this->productUpdater->update($product, [
            'categories' => \array_merge(
                $setCategories->categoryCodes(),
                $this->getNonViewableCategoryCodes($productIdentifier, $userId)
            ),
        ]);
    }

    /**
     * @return string[]
     */
    private function getNonViewableCategoryCodes(ProductIdentifier $productIdentifier, int $userId): array
    {
        $currentCategoryCodes = $this->getCategoryCodes->fromProductIdentifiers([$productIdentifier])[$productIdentifier->asString()] ?? [];
        if ([] === $currentCategoryCodes) {
            return [];
        }

        $viewableCategoryCodes = $this->getViewableCategories->forUserId($currentCategoryCodes, $userId);

        return \array_diff($currentCategoryCodes, $viewableCategoryCodes);
    }
}
