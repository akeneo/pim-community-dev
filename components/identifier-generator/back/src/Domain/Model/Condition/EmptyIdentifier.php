<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class EmptyIdentifier implements ConditionInterface
{
    public function __construct(
        private readonly string $identifierCode
    ) {
    }

    public function normalize(): array
    {
        throw new \LogicException('This component should not be normalized');
    }

    public function identifierCode(): string
    {
        return $this->identifierCode;
    }
}
