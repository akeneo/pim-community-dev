<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Application\Applier;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\AddMultiReferenceEntityValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\UserIntent;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class AddMultiReferenceEntityValueApplier implements UserIntentApplier
{
    public function __construct(
        private ObjectUpdaterInterface $productUpdater,
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function apply(UserIntent $userIntent, ProductInterface $product, int $userId): void
    {
        Assert::isInstanceOf($userIntent, AddMultiReferenceEntityValue::class);

        $formerRecordCodeCollection = $product->getValue(
            $userIntent->attributeCode(),
            $userIntent->localeCode(),
            $userIntent->channelCode(),
        );

        $formerRecordCodes = \array_map(
            fn (RecordCode $recordCode): string => $recordCode->__toString(),
            $formerRecordCodeCollection?->getData() ?? []
        );

        $updatedRecordCodes = \array_values(\array_unique(array_merge($formerRecordCodes, $userIntent->recordCodes())));

        if (\count(\array_diff($updatedRecordCodes, $formerRecordCodes)) === 0) {
            return;
        }

        $this->productUpdater->update(
            $product,
            [
                'values' => [
                    $userIntent->attributeCode() => [
                        [
                            'locale' => $userIntent->localeCode(),
                            'scope' => $userIntent->channelCode(),
                            'data' => $updatedRecordCodes,
                        ],
                    ],
                ],
            ]
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getSupportedUserIntents(): array
    {
        return [AddMultiReferenceEntityValue::class];
    }
}
