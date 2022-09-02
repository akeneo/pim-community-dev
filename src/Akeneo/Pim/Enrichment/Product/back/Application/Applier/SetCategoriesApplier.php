<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Application\Applier;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetCategories;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\UserIntent;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SetCategoriesApplier implements UserIntentApplier
{
    public function __construct(private ObjectUpdaterInterface $productUpdater)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function apply(UserIntent $setCategories, ProductInterface $product, int $userId): void
    {
        Assert::isInstanceOf($setCategories, SetCategories::class);

        // we only send categories viewed by user but they are later merged with non viewabled ones
        $this->productUpdater->update($product, [
            'categories' => $setCategories->categoryCodes(),
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function getSupportedUserIntents(): array
    {
        return [SetCategories::class];
    }
}
