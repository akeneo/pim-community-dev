<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Component\Model;

use Webmozart\Assert\Assert;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class MainIdentifier
{
    public function __construct(public readonly string $mainIdentifierCode)
    {
    }

    public static function fromString(string $mainIdentifierCode): self
    {
        Assert::stringNotEmpty($mainIdentifierCode);

        return new self($mainIdentifierCode);
    }

    public function asString(): string
    {
        return $this->mainIdentifierCode;
    }
}
