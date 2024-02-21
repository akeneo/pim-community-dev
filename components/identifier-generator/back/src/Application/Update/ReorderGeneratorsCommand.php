<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Application\Update;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGeneratorCode;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ReorderGeneratorsCommand
{
    /**
     * @param IdentifierGeneratorCode[] $codes
     */
    private function __construct(public readonly array $codes)
    {
    }

    /**
     * @param string[] $codes
     */
    public static function fromCodes(array $codes): self
    {
        return new self(\array_map(
            static fn (string $code): IdentifierGeneratorCode => IdentifierGeneratorCode::fromString($code),
            $codes
        ));
    }
}
