<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Repository;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\NomenclatureDefinition;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository\ReferenceEntityNomenclatureRepository;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InMemoryReferenceEntityNomenclatureRepository implements ReferenceEntityNomenclatureRepository
{
    /** @var array<string, NomenclatureDefinition> */
    public array $nomenclatureDefinitions = [];

    public function get(string $attributeCode): ?NomenclatureDefinition
    {
        return $this->nomenclatureDefinitions[\mb_strtolower($attributeCode)] ?? null;
    }

    public function update(string $attributeCode, NomenclatureDefinition $nomenclatureDefinition): void
    {
        if (\array_key_exists(\mb_strtolower($attributeCode), $this->nomenclatureDefinitions)) {
            $values = $this->nomenclatureDefinitions[\mb_strtolower($attributeCode)]->values();
        } else {
            $values = [];
        }

        foreach ($nomenclatureDefinition->values() as $recordCode => $value) {
            if (null === $value) {
                unset($values[\mb_strtolower($recordCode)]);
            } else {
                $values[\mb_strtolower($recordCode)] = $value;
            }
        }

        $this->nomenclatureDefinitions[\mb_strtolower($attributeCode)] = new NomenclatureDefinition(
            $nomenclatureDefinition->operator(),
            $nomenclatureDefinition->value(),
            $nomenclatureDefinition->generateIfEmpty(),
            $values
        );
    }
}
