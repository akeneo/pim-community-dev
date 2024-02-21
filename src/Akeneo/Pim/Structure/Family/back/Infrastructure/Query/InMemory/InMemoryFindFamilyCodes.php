<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Family\Infrastructure\Query\InMemory;

use Akeneo\Pim\Structure\Family\ServiceAPI\Query\FamilyQuery;
use Akeneo\Pim\Structure\Family\ServiceAPI\Query\FindFamilyCodes;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InMemoryFindFamilyCodes implements FindFamilyCodes
{
    /** @var string[] $familyCodes */
    private array $familyCodes = [];

    public function fromQuery(FamilyQuery $query): array
    {
        return $this->familyCodes;
    }

    public function save(string $familyCode)
    {
        $this->familyCodes[] = $familyCode;
    }
}
