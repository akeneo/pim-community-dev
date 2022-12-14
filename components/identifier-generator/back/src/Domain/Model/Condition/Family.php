<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\ProductProjection;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Family implements ConditionInterface
{
    static function type(): string
    {
        return 'family';
    }

    static function fromNormalized(): self
    {
        // TODO: Implement normalize() method.
        return new self();
    }

    public function normalize(): array
    {
        // TODO: Implement normalize() method.
    }

    public function match(ProductProjection $productProjection): bool
    {
        // TODO: Implement match() method.
    }
}
