<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Application\Create;

use Akeneo\Pim\Automation\IdentifierGenerator\Application\CommandInterface;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\ConditionInterface;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\PropertyInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class CreateGeneratorCommand implements CommandInterface
{
    /**
     * @param string $id
     * @param string $code
     * @param ConditionInterface[] $conditions
     * @param PropertyInterface[] $structure
     * @param array<string, string> $labels
     * @param string $target
     * @param string|null $delimiter
     */
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
