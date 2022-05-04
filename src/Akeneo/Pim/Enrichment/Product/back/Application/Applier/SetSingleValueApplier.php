<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Application\Applier;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetBooleanValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFileValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetIdentifierValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetImageValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetNumberValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetSimpleSelectValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextareaValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\UserIntent;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class SetSingleValueApplier implements UserIntentApplier
{
    public function __construct(
        private ObjectUpdaterInterface $productUpdater,
    ) {
    }

    public function apply(UserIntent $userIntent, ProductInterface $product, int $userId): void
    {
        if (!$userIntent instanceof SetTextValue
            && !$userIntent instanceof SetNumberValue
            && !$userIntent instanceof SetTextareaValue
            && !$userIntent instanceof SetBooleanValue
            && !$userIntent instanceof SetSimpleSelectValue
            && !$userIntent instanceof SetIdentifierValue
            && !$userIntent instanceof SetFileValue
            && !$userIntent instanceof SetImageValue
        ) {
            throw new \InvalidArgumentException('Not expected class');
        }
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
        return [
            SetTextValue::class,
            SetNumberValue::class,
            SetTextareaValue::class,
            SetBooleanValue::class,
            SetSimpleSelectValue::class,
            SetIdentifierValue::class,
            SetFileValue::class,
            SetImageValue::class,
        ];
    }
}
