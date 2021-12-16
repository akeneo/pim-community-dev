<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredExport\Test\Acceptance\FakeServices\Measurement;

use Akeneo\Platform\TailoredExport\Domain\Query\FindUnitSymbolInterface;

final class InMemoryFindUnitSymbol implements FindUnitSymbolInterface
{
    private array $units = [];

    public function addUnitSymbol(string $familyCode, string $unitCode, string $symbol): void
    {
        $this->units[$familyCode][$unitCode]['symbol'] = $symbol;
    }

    public function byFamilyCodeAndUnitCode(string $familyCode, string $unitCode): ?string
    {
        return $this->units[$familyCode][$unitCode]['symbol'] ?? null;
    }
}
