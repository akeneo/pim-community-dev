<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Repository;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository\NomenclatureValueRepository;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InMemoryNomenclatureValueRepository implements NomenclatureValueRepository
{
    /**
     * @var array<string, string>
     */
    private array $values = [];

    /**
     * @{inheritdoc}
     */
    public function update(string $propertyCode, array $values): void
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
