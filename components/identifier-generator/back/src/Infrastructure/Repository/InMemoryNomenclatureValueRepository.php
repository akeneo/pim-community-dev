<?php

namespace Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Repository;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository\NomenclatureValueRepository;

class InMemoryNomenclatureValueRepository implements NomenclatureValueRepository
{
    /**
     * @var array<string, string>
     */
    private array $values = [];

    public function update(array $values): void
    {
        foreach ($values as $familyCode => $value) {
            if (null === $value) {
                unset($this->values[$familyCode]);
            } else {
                $this->values[$familyCode] = $value;
            }
        }
    }

    public function get(string $familyCode): ?string
    {
        return $this->values[$familyCode] ?? null;
    }
}
