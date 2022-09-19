<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class Conditions
{
    /**
     * @param ConditionInterface[] $conditions
     */
    public function __construct(
        private array $conditions,
    )
    {
    }
}
