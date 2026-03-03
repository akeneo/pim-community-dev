<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Application\Applier;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductPrice;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ClearPriceValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\UserIntent;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ClearPriceValueApplier implements UserIntentApplier
{
    public function __construct(
        private ObjectUpdaterInterface $productUpdater,
    ) {
    }

    public function apply(UserIntent $userIntent, ProductInterface $product, int $userId): void
    {
        Assert::isInstanceOf($userIntent, ClearPriceValue::class);

        $previousPriceCollection = $product->getValue(
            $userIntent->attributeCode(),
            $userIntent->localeCode(),
            $userIntent->channelCode()
        )?->getData()->toArray() ?? [];

        $newPriceCollection = array_values(array_filter(
            $previousPriceCollection,
            static fn (ProductPrice $value) => $value->getCurrency() !== $userIntent->currencyCode()
        ));

        $normalizedPriceCollection = array_map(static fn (ProductPrice $value) => [
            'amount' => $value->getData(),
            'currency' => $value->getCurrency(),
        ], $newPriceCollection);

        $this->productUpdater->update($product, [
            'values' => [
                $userIntent->attributeCode() => [
                    [
                        'scope' => $userIntent->channelCode(),
                        'locale' => $userIntent->localeCode(),
                        'data' => $normalizedPriceCollection,
                    ],
                ],
            ],
        ]);
    }

    public function getSupportedUserIntents(): array
    {
        return [ClearPriceValue::class];
    }
}
