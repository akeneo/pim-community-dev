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
    public function __construct(
        public string $id,
        public string $code,
        public array $conditions,
        public array $structure,
        public array $labels,
        public string $target,
        public ?string $delimiter,
    ) {
    }
}
