<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Completeness\Model;

use Akeneo\Pim\Structure\Component\Query\PublicApi\Family\RequiredAttributesMask;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Family\RequiredAttributesMaskForChannelAndLocale;
use Ramsey\Uuid\UuidInterface;

/**
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CompletenessProductMask
{
    // TODO - TIP-1212: familyCode should not be nullable
    public function __construct(
        private string $id,
        private ?string $familyCode,
        private array $mask
    ) {
    }

    public function familyCode(): ?string
    {
        return $this->familyCode;
    }

    public function mask(): array
    {
        return $this->mask;
    }

    public function id(): string
    {
        return $this->id;
    }

    // TODO: TIP-1212: remove null on accepted argument for the mask (a product could be currently without a family)
    public function completenessCollectionForProduct(?RequiredAttributesMask $attributeRequirementMask)
    {
        if (null === $this->familyCode && null !== $attributeRequirementMask) {
            throw new \InvalidArgumentException('You cannot provide an attribute requirement mask when a product is not in a family.');
        } elseif (null !== $this->familyCode && null === $attributeRequirementMask) {
            throw new \InvalidArgumentException('You have to provide an attribute requirement mask when a product is in a family.');
        } elseif (null === $this->familyCode && null === $attributeRequirementMask) {
            return new ProductCompletenessWithMissingAttributeCodesCollection($this->id, []);
        } else {
            $productCompletenesses = array_map(
                function (RequiredAttributesMaskForChannelAndLocale $attributeRequirementMaskPerLocaleAndChannel): ProductCompletenessWithMissingAttributeCodes {
                    return $this->completenessForChannelAndLocale($this->mask, $attributeRequirementMaskPerLocaleAndChannel);
                },
                $attributeRequirementMask->masks()
            );

            return new ProductCompletenessWithMissingAttributeCodesCollection($this->id, $productCompletenesses);
        }
    }

    private function completenessForChannelAndLocale(array $productMask, RequiredAttributesMaskForChannelAndLocale $attributeRequirementMaskPerChannelAndLocale): ProductCompletenessWithMissingAttributeCodes
    {
        $difference = array_diff($attributeRequirementMaskPerChannelAndLocale->mask(), $productMask);

        $missingAttributeCodes = array_map(function (string $mask) : string {
            return substr($mask, 0, strpos($mask, RequiredAttributesMaskForChannelAndLocale::ATTRIBUTE_CHANNEL_LOCALE_SEPARATOR));
        }, $difference);

        return new ProductCompletenessWithMissingAttributeCodes(
            $attributeRequirementMaskPerChannelAndLocale->channelCode(),
            $attributeRequirementMaskPerChannelAndLocale->localeCode(),
            count($attributeRequirementMaskPerChannelAndLocale->mask()),
            $missingAttributeCodes
        );
    }
}
