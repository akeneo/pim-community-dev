<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Domain\Model\Attribute;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOption\AttributeOption;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Webmozart\Assert\Assert;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class OptionAttribute extends AbstractAttribute
{
    private const ATTRIBUTE_TYPE = 'option';

    /** @var AttributeOption[] */
    private $attributeOptions = [];

    public static function create(
        AttributeIdentifier $identifier,
        ReferenceEntityIdentifier $referenceEntityIdentifier,
        AttributeCode $code,
        LabelCollection $labelCollection,
        AttributeOrder $order,
        AttributeIsRequired $isRequired,
        AttributeValuePerChannel $valuePerChannel,
        AttributeValuePerLocale $valuePerLocale
    ) : self {
        return new self(
            $identifier,
            $referenceEntityIdentifier,
            $code,
            $labelCollection,
            $order,
            $isRequired,
            $valuePerChannel,
            $valuePerLocale
        );
    }

    public function setOptions(array $attributeOptions): void
    {
        $this->assertNoDuplicates($attributeOptions);

        $this->attributeOptions = $attributeOptions;
    }

    public function normalize(): array
    {
        return array_merge(
            parent::normalize(),
            [
                'attribute_options' => array_map(
                    function (AttributeOption $attributeOption) {
                        return $attributeOption->normalize();
                    },
                    $this->attributeOptions
                ),
            ]
        );
    }

    /**
     * @return AttributeOption[]
     */
    public function getAttributeOptions(): array
    {
        return $this->attributeOptions;
    }

    protected function getType(): string
    {
        return self::ATTRIBUTE_TYPE;
    }

    /**
     * @param AttributeOption[] $attributeOptions
     */
    private function assertNoDuplicates(array $attributeOptions): void
    {
        $optionCodes = array_map(
            function (AttributeOption $attributeOption) {
                return $attributeOption->getCode();
            },
            $attributeOptions
        );
        $uniqueCodes = array_unique($optionCodes);
        Assert::eq(\count($optionCodes), \count($uniqueCodes), 'Expected to have a unique set of option codes');
    }
}
