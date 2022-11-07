<?php

declare(strict_types=1);

namespace Akeneo\Category\Domain\ValueObject\Attribute\Value;

use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ImageValue
{
    public function __construct(public readonly array $value)
    {
        Assert::allString($value);
        Assert::allStringNotEmpty($this->$value);
    }

    public static function fromArray(array $value): self
    {
        return new self($value);
    }
}
