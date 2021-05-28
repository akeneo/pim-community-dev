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

namespace Akeneo\AssetManager\Domain\Model\Attribute;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Webmozart\Assert\Assert;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class NumberAttribute extends AbstractAttribute
{
    public const ATTRIBUTE_TYPE = 'number';

    private AttributeDecimalsAllowed $decimalsAllowed;

    private AttributeLimit $minValue;

    private AttributeLimit $maxValue;

    private function __construct(
        AttributeIdentifier $identifier,
        AssetFamilyIdentifier $assetFamilyIdentifier,
        AttributeCode $code,
        LabelCollection $labelCollection,
        AttributeOrder $order,
        AttributeIsRequired $isRequired,
        AttributeIsReadOnly $isReadOnly,
        AttributeValuePerChannel $valuePerChannel,
        AttributeValuePerLocale $valuePerLocale,
        AttributeDecimalsAllowed $decimalsAllowed,
        AttributeLimit $minValue,
        AttributeLimit $maxValue
    ) {
        parent::__construct(
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

        $this->checkMinIsLessThanMax($minValue, $maxValue);
        $this->decimalsAllowed = $decimalsAllowed;
        $this->minValue = $minValue;
        $this->maxValue = $maxValue;
    }

    public static function create(
        AttributeIdentifier $identifier,
        AssetFamilyIdentifier $assetFamilyIdentifier,
        AttributeCode $code,
        LabelCollection $labelCollection,
        AttributeOrder $order,
        AttributeIsRequired $isRequired,
        AttributeIsReadOnly $isReadOnly,
        AttributeValuePerChannel $valuePerChannel,
        AttributeValuePerLocale $valuePerLocale,
        AttributeDecimalsAllowed $decimalsAllowed,
        AttributeLimit $minValue,
        AttributeLimit $maxValue
    ) {
        return new self(
            $identifier,
            $assetFamilyIdentifier,
            $code,
            $labelCollection,
            $order,
            $isRequired,
            $isReadOnly,
            $valuePerChannel,
            $valuePerLocale,
            $decimalsAllowed,
            $minValue,
            $maxValue
        );
    }

    public function normalize(): array
    {
        return array_merge(
            parent::normalize(),
            [
                'decimals_allowed' => $this->decimalsAllowed->normalize(),
                'min_value'  => $this->minValue->normalize(),
                'max_value'  => $this->maxValue->normalize(),
            ]
        );
    }

    public function getType(): string
    {
        return self::ATTRIBUTE_TYPE;
    }

    public function setDecimalsAllowed(AttributeDecimalsAllowed $decimalsAllowed): void
    {
        $this->decimalsAllowed = $decimalsAllowed;
    }

    public function allowsDecimalValues(): bool
    {
        return $this->decimalsAllowed->normalize();
    }

    public function isMinLimitless(): bool
    {
        return $this->minValue->isLimitLess();
    }

    public function isMaxLimitless(): bool
    {
        return $this->maxValue->isLimitLess();
    }

    public function minValue(): string
    {
        return $this->minValue->normalize();
    }

    public function maxValue(): string
    {
        return $this->maxValue->normalize();
    }

    public function setLimit(AttributeLimit $minValue, AttributeLimit $maxValue): void
    {
        $this->checkMinIsLessThanMax($minValue, $maxValue);
        $this->minValue = $minValue;
        $this->maxValue = $maxValue;
    }

    private function checkMinIsLessThanMax(AttributeLimit $minValue, AttributeLimit $maxValue): void
    {
        if (!$minValue->isLimitLess() && !$maxValue->isLimitLess()) {
            Assert::false(
                $minValue->isGreater($maxValue),
                'Cannot create attribute with a min limit greater than the max limit'
            );
        }
    }
}
