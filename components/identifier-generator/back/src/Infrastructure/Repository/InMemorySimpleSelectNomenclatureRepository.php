<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Repository;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\NomenclatureDefinition;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository\SimpleSelectNomenclatureRepository;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InMemorySimpleSelectNomenclatureRepository implements SimpleSelectNomenclatureRepository
{
    /** @var array<string, NomenclatureDefinition> */
    private array $nomenclatureDefinitions = [];
    /**
     * @var array<string, string>
     */
    private array $values = [];

    public function get(string $attributeCode): ?NomenclatureDefinition
    {
        return $this->nomenclatureDefinitions[$attributeCode] ?? null;
    }

    public function update(string $attributeCode, NomenclatureDefinition $nomenclatureDefinition): void
    {
        foreach ($nomenclatureDefinition->values() as $attributeOptionCode => $value) {
            if (null === $value) {
                unset($this->values[$attributeOptionCode]);
            } else {
                $this->values[$attributeOptionCode] = $value;
            }
        }

        $this->nomenclatureDefinitions[$attributeCode] = new NomenclatureDefinition(
            $nomenclatureDefinition->operator(),
            $nomenclatureDefinition->value(),
            $nomenclatureDefinition->generateIfEmpty(),
            $this->values
        );
    }
}
