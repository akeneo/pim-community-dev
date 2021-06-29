<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject;

use Akeneo\Pim\Structure\Component\AttributeTypes;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
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
