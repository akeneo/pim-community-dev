<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Application\Applier;

use Akeneo\Pim\Enrichment\Component\Product\EntityWithFamilyVariant\RemoveParentInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ConvertToSimpleProduct;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\UserIntent;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ConvertToSimpleProductApplier implements UserIntentApplier
{
    public function __construct(
        private RemoveParentInterface $removeParent
    ) {
    }

    public function apply(UserIntent $userIntent, ProductInterface $product, int $userId): void
    {
        Assert::isInstanceOf($userIntent, ConvertToSimpleProduct::class);
        if (!$product->isVariant()) {
            return;
        }

        $this->removeParent->from($product);
    }

    public function getSupportedUserIntents(): array
    {
        return [ConvertToSimpleProduct::class];
    }
}
