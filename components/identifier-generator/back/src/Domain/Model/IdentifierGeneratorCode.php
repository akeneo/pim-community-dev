<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model;

use Webmozart\Assert\Assert;

/**
 * Unique identifier code
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class IdentifierGeneratorCode
{
    private function __construct(
        private string $code,
    ) {
    }

    public static function fromString(string $code): self
    {
        Assert::stringNotEmpty($code);

        return new self($code);
    }

    public function asString(): string
    {
        return $this->code;
    }
}
