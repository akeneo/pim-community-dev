<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @phpstan-import-type EnabledNormalized from Enabled
 */
interface ConditionInterface
{
    /**
     * @return EnabledNormalized
     */
    public function normalize(): array;
}
