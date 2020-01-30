<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Domain\Model\Attribute;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOption\AttributeOption;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOption\OptionCode;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Webmozart\Assert\Assert;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class OptionAttribute extends AbstractAttribute
{
    public const ATTRIBUTE_TYPE = 'option';

    /** @var AttributeOption[] */
    private $attributeOptions = [];

    public static function create(
        AttributeIdentifier $identifier,
        AssetFamilyIdentifier $assetFamilyIdentifier,
        AttributeCode $code,
        LabelCollection $labelCollection,
        AttributeOrder $order,
        AttributeIsRequired $isRequired,
        AttributeIsReadOnly $isReadOnly,
        AttributeValuePerChannel $valuePerChannel,
        AttributeValuePerLocale $valuePerLocale
    ) : self {
        return new self(
            $identifier,
            $assetFamilyIdentifier,
            $code,
            $labelCollection,
            $order,
            $isRequired,
            $isReadOnly,
            $valuePerChannel,
            $valuePerLocale
        );
    }

    public function setOptions(array $attributeOptions): void
    {
        Assert::allIsInstanceOf($attributeOptions, AttributeOption::class);
        $this->attributeOptions = [];

        foreach ($attributeOptions as $attributeOption) {
            $optionCode = (string) $attributeOption->getCode();
            if (array_key_exists($optionCode, $this->attributeOptions)) {
                throw new \InvalidArgumentException('Expected to have a unique set of option codes');
            }

            $this->attributeOptions[$optionCode] = $attributeOption;
        }
    }

    public function addOption(AttributeOption $option): void
    {
        Assert::false(isset($this->attributeOptions[(string) $option->getCode()]), 'Option already exists in the collection');

        $this->attributeOptions[(string) $option->getCode()] = $option;
    }

    public function updateOption(AttributeOption $option): void
    {
        Assert::true(isset($this->attributeOptions[(string) $option->getCode()]), 'Option cannot be set as it does not exist');

        $this->attributeOptions[(string) $option->getCode()] = $option;
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

    public function getAttributeOption(OptionCode $code): AttributeOption
    {
        if (!isset($this->attributeOptions[(string) $code])) {
            throw new \InvalidArgumentException(sprintf('Attribute option "%s" does not exist.', (string) $code));
        }

        return $this->attributeOptions[(string) $code];
    }

    public function getType(): string
    {
        return self::ATTRIBUTE_TYPE;
    }
}
