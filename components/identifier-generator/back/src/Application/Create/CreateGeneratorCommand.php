<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Application\Create;

use Akeneo\Pim\Automation\IdentifierGenerator\Application\CommandInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class CreateGeneratorCommand implements CommandInterface
{
    /**
     * @param list<array<string, mixed>> $conditions
     * @param list<array<string, mixed>> $structure
     * @param array<string, string> $labels
     */
    public function __construct(
        public readonly string $code,
        public readonly array $conditions,
        public readonly array $structure,
        public readonly array $labels,
        public readonly string $target,
        public readonly ?string $delimiter,
        public readonly string $textTransformation,
    ) {
    }
}
