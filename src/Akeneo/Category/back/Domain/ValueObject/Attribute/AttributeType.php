<?php

declare(strict_types=1);

namespace Akeneo\Category\Domain\ValueObject\Attribute;

use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class AttributeType
{
    public const TEXT = 'text';
    public const TEXTAREA = 'textarea';
    public const RICH_TEXT = 'richtext';
    public const IMAGE = 'image';

    public const ATTRIBUTE_TYPES = [
        self::TEXT,
        self::TEXTAREA,
        self::RICH_TEXT,
        self::IMAGE,
    ];

    /**
     * @param string $attributeType The attribute type expected
     *
     * @see AttributeType::ATTRIBUTE_TYPES
     */
    public function __construct(private string $attributeType)
    {
        Assert::string($this->attributeType);
        Assert::oneOf($this->attributeType, self::ATTRIBUTE_TYPES);
    }

    public function __toString(): string
    {
        return $this->attributeType;
    }
}
