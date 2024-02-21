<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Repository;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\NomenclatureDefinition;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository\FamilyNomenclatureRepository;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InMemoryFamilyNomenclatureRepository implements FamilyNomenclatureRepository
{
    private ?NomenclatureDefinition $nomenclatureDefinition = null;
    /**
     * @var array<string, string>
     */
    private array $values = [];

    public function get(): ?NomenclatureDefinition
    {
        return $this->nomenclatureDefinition;
    }

    public function update(NomenclatureDefinition $nomenclatureDefinition): void
    {
        foreach ($nomenclatureDefinition->values() as $familyCode => $value) {
            if (null === $value) {
                unset($this->values[\mb_strtolower($familyCode)]);
            } else {
                $this->values[\mb_strtolower($familyCode)] = $value;
            }
        }

        $this->nomenclatureDefinition = new NomenclatureDefinition(
            $nomenclatureDefinition->operator(),
            $nomenclatureDefinition->value(),
            $nomenclatureDefinition->generateIfEmpty(),
            $this->values
        );
    }
}
