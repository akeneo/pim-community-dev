<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Completeness\Model;

use Akeneo\Pim\Enrichment\Component\Product\Model\Projection\ProductCompletenessWithMissingAttributeCodes;
use Akeneo\Pim\Enrichment\Component\Product\Model\Projection\ProductCompletenessWithMissingAttributeCodesCollection;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Family\CompletenessFamilyMask;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Family\CompletenessFamilyMaskPerChannelAndLocale;

/**
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CompletenessProductMask
{
    /** @var int */
    private $id;

    /** @var string */
    private $identifier;

    /** @var string */
    private $familyCode;

    /** @var array */
    private $mask;

    // TODO - TIP-1212: familyCode should not be nullable
    public function __construct(
        int $id,
        string $identifier,
        ?string $familyCode,
        array $mask
    ) {
        $this->id = $id;
        $this->identifier = $identifier;
        $this->familyCode = $familyCode;
        $this->mask = $mask;
    }

    public function familyCode(): ?string
    {
        return $this->familyCode;
    }

    public function mask(): array
    {
        return $this->mask;
    }

    public function id(): int
    {
        return $this->id;
    }

    public function identifier(): string
    {
        return $this->identifier;
    }

    // TODO: TIP-1212: remove null on accepted argument for the mask (a product could be currently without a family)
    public function completenessCollectionForProduct(?CompletenessFamilyMask $attributeRequirementMask)
    {
        if (null === $this->familyCode && null !== $attributeRequirementMask) {
            throw new \InvalidArgumentException('You cannot provide an attribute requirement mask when a product is not in a family.');
        }
        else if (null !== $this->familyCode && null === $attributeRequirementMask) {
            throw new \InvalidArgumentException('You have to provide an attribute requirement mask when a product is in a family.');
        }
        else if (null === $this->familyCode && null === $attributeRequirementMask) {
            return new ProductCompletenessWithMissingAttributeCodesCollection($this->id, []);
        } else {
            $productCompletenesses = array_map(
                function (CompletenessFamilyMaskPerChannelAndLocale $attributeRequirementMaskPerLocaleAndChannel): ProductCompletenessWithMissingAttributeCodes {
                    return $this->completenessForChannelAndLocale($this->mask, $attributeRequirementMaskPerLocaleAndChannel);
                },
                $attributeRequirementMask->masks()
            );

            return new ProductCompletenessWithMissingAttributeCodesCollection($this->id, $productCompletenesses);
        }
    }

    private function completenessForChannelAndLocale(array $productMask, CompletenessFamilyMaskPerChannelAndLocale $attributeRequirementMaskPerChannelAndLocale): ProductCompletenessWithMissingAttributeCodes
    {
        $difference = array_diff($attributeRequirementMaskPerChannelAndLocale->mask(), $productMask);

        $missingAttributeCodes = array_map(function (string $mask) : string {
            return substr($mask, 0, strpos($mask, CompletenessFamilyMaskPerChannelAndLocale::ATTRIBUTE_CHANNEL_LOCALE_SEPARATOR));
        }, $difference);

        return new ProductCompletenessWithMissingAttributeCodes(
            $attributeRequirementMaskPerChannelAndLocale->channelCode(),
            $attributeRequirementMaskPerChannelAndLocale->localeCode(),
            count($attributeRequirementMaskPerChannelAndLocale->mask()),
            $missingAttributeCodes
        );
    }
}
