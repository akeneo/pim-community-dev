<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject;

use Akeneo\Pim\Structure\Component\AttributeTypes;

final class AttributeType
{
    public const EVALUABLE_ATTRIBUTE_TYPES = [
        AttributeTypes::TEXT,
        AttributeTypes::TEXTAREA,
        AttributeTypes::OPTION_SIMPLE_SELECT,
        AttributeTypes::OPTION_MULTI_SELECT,
    ];

    /** @var string */
    private $type;

    public function __construct(string $code)
    {
        $this->type = $code;
    }

    public function __toString()
    {
        return $this->type;
    }

    public function equals(AttributeType $attributeType): bool
    {
        return $this->type === strval($attributeType);
    }

    public static function text(): self
    {
        return new self(AttributeTypes::TEXT);
    }

    public static function textarea(): self
    {
        return new self(AttributeTypes::TEXTAREA);
    }

    public static function simpleSelect(): self
    {
        return new self(AttributeTypes::OPTION_SIMPLE_SELECT);
    }

    public static function multiSelect(): self
    {
        return new self(AttributeTypes::OPTION_MULTI_SELECT);
    }
}
