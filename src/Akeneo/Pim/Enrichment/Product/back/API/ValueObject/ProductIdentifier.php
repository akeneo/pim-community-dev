<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\API\ValueObject;

use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductIdentifier
{
    private function __construct(private string $attributeCode, private string $identifier)
    {
        Assert::notEmpty($this->attributeCode);
        Assert::notEmpty($this->identifier);
    }

    public static function fromAttributeCodeAndIdentifier(string $attributeCode, string $identifier): ProductIdentifier
    {
        return new ProductIdentifier($attributeCode, $identifier);
    }

    public function attributeCode(): string
    {
        return $this->attributeCode;
    }

    public function identifier(): string
    {
        return $this->identifier;
    }
}
