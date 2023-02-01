<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface NomenclatureValueRepository
{
    /**
     * @param array<string, ?string> $values
     */
    public function update(string $propertyCode, array $values): void;

    public function get(string $familyCode): ?string;
}
