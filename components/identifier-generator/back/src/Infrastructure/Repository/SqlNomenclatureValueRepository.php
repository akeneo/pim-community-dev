<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Repository;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository\NomenclatureValueRepository;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SqlNomenclatureValueRepository implements NomenclatureValueRepository
{
    public function set(string $familyCode, ?string $value): void
    {
        // TODO: Implement set() method.
    }

    public function get(string $familyCode): ?string
    {
        // TODO: Implement get() method.
    }
}
