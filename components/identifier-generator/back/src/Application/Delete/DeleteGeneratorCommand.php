<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Application\Delete;

use Akeneo\Pim\Automation\IdentifierGenerator\Application\CommandInterface;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class DeleteGeneratorCommand implements CommandInterface
{
    private function __construct(
        private readonly string $code,
    ) {
    }

    public static function fromCode(string $code): self
    {
        return new self($code);
    }

    public function getIdentifierGeneratorCode(): string
    {
        return $this->code;
    }
}
