<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ReferenceEntity\Domain\Model\Attribute;

use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class NumberAttribute extends AbstractAttribute
{
    private const ATTRIBUTE_TYPE = 'number';

    /** @var AttributeIsDecimal */
    private $isDecimal;

    /** @var AttributeMinValue */
    private $minValue;

    /** @var AttributeMaxValue */
    private $maxValue;

    private function __construct(
        AttributeIdentifier $identifier,
        ReferenceEntityIdentifier $referenceEntityIdentifier,
        AttributeCode $code,
        LabelCollection $labelCollection,
        AttributeOrder $order,
        AttributeIsRequired $isRequired,
        AttributeValuePerChannel $valuePerChannel,
        AttributeValuePerLocale $valuePerLocale,
        AttributeIsDecimal $isDecimal,
        AttributeMinValue $minValue,
        AttributeMaxValue $maxValue
    ) {
        parent::__construct(
            $identifier,
            $referenceEntityIdentifier,
            $code,
            $labelCollection,
            $order,
            $isRequired,
            $valuePerChannel,
            $valuePerLocale
        );

        $this->isDecimal = $isDecimal;
        $this->minValue = $minValue;
        $this->maxValue = $maxValue;
    }

    public static function create(
        AttributeIdentifier $identifier,
        ReferenceEntityIdentifier $referenceEntityIdentifier,
        AttributeCode $code,
        LabelCollection $labelCollection,
        AttributeOrder $order,
        AttributeIsRequired $isRequired,
        AttributeValuePerChannel $valuePerChannel,
        AttributeValuePerLocale $valuePerLocale,
        AttributeIsDecimal $isDecimal,
        AttributeMinValue $minValue,
        AttributeMaxValue $maxValue
    ) {
        return new self(
            $identifier,
            $referenceEntityIdentifier,
            $code,
            $labelCollection,
            $order,
            $isRequired,
            $valuePerChannel,
            $valuePerLocale,
            $isDecimal,
            $minValue,
            $maxValue
        );
    }

    public function normalize(): array
    {
        return array_merge(
            parent::normalize(),
            [
                'is_decimal' => $this->isDecimal->normalize(),
                'min_value' => $this->minValue->normalize(),
                'max_value' => $this->maxValue->normalize(),
            ]
        );
    }

    public function getType(): string
    {
        return self::ATTRIBUTE_TYPE;
    }

    public function setIsDecimal(AttributeIsDecimal $isDecimal): void
    {
        $this->isDecimal = $isDecimal;
    }

    public function setMinValue(AttributeMinValue $attributeMinValue): void
    {
        $this->minValue = $attributeMinValue;
    }

    public function setMaxValue(AttributeMaxValue $attributeMaxValue): void
    {
        $this->maxValue = $attributeMaxValue;
    }
}
