<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\ProductProjection;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @phpstan-import-type EnabledNormalized from Enabled
 * @phpstan-import-type FamilyNormalized from Family
 * @phpstan-import-type SimpleSelectNormalized from SimpleSelect
 * @phpstan-type ConditionNormalized EnabledNormalized|FamilyNormalized|SimpleSelectNormalized
 */
interface ConditionInterface
{
    /**
     * @return ConditionNormalized
     */
    public function normalize(): array;

    public function match(ProductProjection $productProjection): bool;
}
