<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property;

use Webmozart\Assert\Assert;

/**
 * Property to add a free text to the structure
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class FreeText implements PropertyInterface
{
    private function __construct(
        private string $value,
    ) {
    }

    public static function fromString(string $value)
    {
        Assert::stringNotEmpty($value);
        
        return new self($value);
    }

    public function asString(): string
    {
        return $this->value;
    }
}
