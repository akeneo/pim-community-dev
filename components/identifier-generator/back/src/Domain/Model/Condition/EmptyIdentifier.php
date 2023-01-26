<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\ProductProjection;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class EmptyIdentifier implements ConditionInterface
{
    public function __construct(
    ) {
    }

    public function normalize(): array
    {
        return [];
    }

    public function match(ProductProjection $productProjection): bool
    {
        $identifierValue = $productProjection->identifier();

        return (null === $identifierValue || '' === $identifierValue);
    }
}
