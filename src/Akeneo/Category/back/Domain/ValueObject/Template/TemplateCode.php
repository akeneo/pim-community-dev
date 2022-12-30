<?php

declare(strict_types=1);

namespace Akeneo\Category\Domain\ValueObject\Template;

use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class TemplateCode
{
    public function __construct(private string $code)
    {
        Assert::stringNotEmpty($code);
    }

    public static function fromString(string $code): self
    {
        return new self($code);
    }

    public function __toString(): string
    {
        return $this->code;
    }
}
