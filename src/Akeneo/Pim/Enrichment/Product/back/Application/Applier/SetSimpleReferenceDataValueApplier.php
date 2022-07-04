<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Application\Applier;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetSimpleReferenceDataValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\UserIntent;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class SetSimpleReferenceDataValueApplier implements UserIntentApplier
{
    public function __construct(private ObjectUpdaterInterface $productUpdater)
    {
    }

    public function apply(UserIntent $userIntent, ProductInterface $product, int $userId): void
    {
        Assert::isInstanceOf($userIntent, SetSimpleReferenceDataValue::class);
        $this->productUpdater->update($product, [
            'values' => [
                $userIntent->attributeCode() => [
                    [
                        'locale' => $userIntent->localeCode(),
                        'scope' => $userIntent->channelCode(),
                        'data' => $userIntent->value(),
                    ],
                ],
            ],
        ]);
    }

    public function getSupportedUserIntents(): array
    {
        return [SetSimpleReferenceDataValue::class];
    }
}
