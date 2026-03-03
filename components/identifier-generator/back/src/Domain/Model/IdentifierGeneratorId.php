<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model;

use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class IdentifierGeneratorId
{
    private function __construct(
        private readonly string $id,
    ) {
    }

    public static function fromString(string $id): self
    {
        Assert::uuid($id);

        return new self($id);
    }

    public function asString(): string
    {
        return $this->id;
    }
}
