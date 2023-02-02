<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Repository;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\NomenclatureDefinition;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository\NomenclatureRepository;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InMemoryNomenclatureRepository implements NomenclatureRepository
{
    /** @var array<string, NomenclatureDefinition> */
    private array $nomenclatureDefinitions = [];
    /**
     * @var array<string, string>
     */
    private array $values = [];

    public function get(string $propertyCode): ?NomenclatureDefinition
    {
        return $this->nomenclatureDefinitions[$propertyCode] ?? null;
    }

    public function update(string $propertyCode, NomenclatureDefinition $nomenclatureDefinition): void
    {
        $this->nomenclatureDefinitions[$propertyCode] = $nomenclatureDefinition;

        foreach (($nomenclatureDefinition->values() ?? []) as $familyCode => $value) {
            if (null === $value) {
                unset($this->values[$familyCode]);
            } else {
                $this->values[$familyCode] = $value;
            }
        }
    }
}
