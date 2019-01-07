<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Domain\Model\Attribute;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOption\AttributeOption;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOption\OptionCode;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Webmozart\Assert\Assert;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class OptionCollectionAttribute extends AbstractAttribute
{
    private const ATTRIBUTE_TYPE = 'option_collection';
    private const MAX_OPTIONS = 100;

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
        Assert::maxCount(
            $attributeOptions,
            self::MAX_OPTIONS,
            sprintf(
                'A multiselect attribute can have a maximum of %d options, %d given',
                self::MAX_OPTIONS,
                \count($attributeOptions)
            )
        );

        $this->attributeOptions = [];

        foreach ($attributeOptions as $attributeOption) {
            $optionCode = (string) $attributeOption->getCode();
            if (array_key_exists($optionCode, $this->attributeOptions)) {
                throw new \InvalidArgumentException('Expected to have a unique set of option codes');
            }

            $this->attributeOptions[$optionCode] = $attributeOption;
        }
    }

    public function normalize(): array
    {
        return array_merge(
            parent::normalize(),
            [
                'options' => array_map(
                    function (AttributeOption $attributeOption) {
                        return $attributeOption->normalize();
                    },
                    $this->getAttributeOptions()
                ),
            ]
        );
    }

    /**
     * @return AttributeOption[]
     */
    public function getAttributeOptions(): array
    {
        return array_values($this->attributeOptions);
    }

    public function hasAttributeOption(OptionCode $optionCode): bool
    {
        return array_key_exists((string) $optionCode, $this->attributeOptions);
    }

    protected function getType(): string
    {
        return self::ATTRIBUTE_TYPE;
    }
}
