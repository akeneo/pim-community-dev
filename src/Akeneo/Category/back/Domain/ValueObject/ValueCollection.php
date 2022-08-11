<?php

namespace Akeneo\Category\Domain\ValueObject;

use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ValueCollection
{
    /**
     * @param array<string, string> $values
     */
    private function __construct(private ?array $values)
    {
        Assert::allString($values);
        Assert::allStringNotEmpty(\array_keys($values));
    }

    /**
     * @param array<string, string> $values
     */
    public static function fromArray(array $values): self
    {
        return new self($values);
    }
}
