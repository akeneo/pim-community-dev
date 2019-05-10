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
use Webmozart\Assert\Assert;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class NumberAttribute extends AbstractAttribute
{
    private const ATTRIBUTE_TYPE = 'number';

    /** @var AttributeIsDecimal */
    private $isDecimal;

    /** @var AttributeLimit */
    private $minValue;

    /** @var AttributeLimit */
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
        AttributeLimit $minValue,
        AttributeLimit $maxValue
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

        if (!$minValue->isLimitLess() && !$maxValue->isLimitLess()) {
            Assert::false($minValue->isGreater($maxValue), 'Cannot create attribute with a min limit greater than the max limit');
        }
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
        AttributeLimit $minValue,
        AttributeLimit $maxValue
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

    public function allowsDecimalValues(): bool
    {
        return $this->isDecimal->normalize();
    }

    public function setLimit(AttributeLimit $minValue, AttributeLimit $maxValue): void
    {
        if (!$minValue->isLimitLess()
            && !$maxValue->isLimitLess()
            && $minValue->isGreater($maxValue)
        ) {
            $message = sprintf(
                'Min value %s, cannot be greater than the current max value (%s)',
                $minValue->normalize(),
                $maxValue->normalize()
            );
            throw new \InvalidArgumentException($message);
        }

        $this->minValue = $minValue;
        $this->maxValue = $maxValue;
    }
}
