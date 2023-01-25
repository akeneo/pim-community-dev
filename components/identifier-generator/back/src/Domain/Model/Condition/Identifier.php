<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\ProductProjection;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class Identifier implements ConditionInterface
{
    public function __construct(
        private readonly string $attributeCode
    ) {
    }

    /**
     * @return 'enabled'
     */
    public static function type(): string
    {
        return 'identifier';
    }

    public function normalize(): array
    {
        return [
            'type' => self::type(),
            'attributeCode' => $this->attributeCode,
            'operator' => 'EMPTY',
        ];
    }

    public function match(ProductProjection $productProjection): bool
    {
        return $productProjection->identifier() === null;
    }

    public function isAuto(): bool
    {
        return true;
    }
}
