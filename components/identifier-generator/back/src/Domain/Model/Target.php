<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model;

use Webmozart\Assert\Assert;

/**
 * The attribute code of type identifier that will be impacted by the generation
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class Target
{
    private function __construct(
        private string $attributeCode,
    ) {
    }

    public static function fromString(string $attributeCode): self
    {
        Assert::stringNotEmpty($attributeCode);

        return new self($attributeCode);
    }

    public function asString(): string
    {
        return $this->attributeCode;
    }
}
