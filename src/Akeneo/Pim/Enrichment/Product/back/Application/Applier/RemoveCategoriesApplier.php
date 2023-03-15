<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Application\Applier;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\RemoveCategories;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\UserIntent;
use Akeneo\Pim\Enrichment\Product\Domain\Query\GetCategoryCodes;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RemoveCategoriesApplier implements UserIntentApplier
{
    public function __construct(
        private ObjectUpdaterInterface $productUpdater,
        private GetCategoryCodes $getCategoryCodes
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function apply(UserIntent $removeCategories, ProductInterface $product, int $userId): void
    {
        Assert::isInstanceOf($removeCategories, RemoveCategories::class);

        $categoryCodes = $this->getCategoryCodes->fromProductUuids([$product->getUuid()]);
        $categoryCodes = $categoryCodes[$product->getUuid()->toString()] ?? [];

        $this->productUpdater->update($product, [
            'categories' => \array_values(\array_diff($categoryCodes, $removeCategories->categoryCodes())),
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function getSupportedUserIntents(): array
    {
        return [RemoveCategories::class];
    }
}
