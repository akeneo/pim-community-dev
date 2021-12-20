<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredExport\Test\Acceptance\FakeServices\Measurement;

use Akeneo\Platform\TailoredExport\Domain\Query\FindUnitSymbolInterface;

final class InMemoryFindUnitSymbol implements FindUnitSymbolInterface
{
    private array $unitSymbols = [];

    public function addUnitSymbol(string $familyCode, string $unitCode, string $symbol): void
    {
        $this->unitSymbols[$familyCode][$unitCode]['symbol'] = $symbol;
    }

    public function byFamilyCodeAndUnitCode(string $familyCode, string $unitCode): ?string
    {
        return $this->unitSymbols[$familyCode][$unitCode]['symbol'] ?? null;
    }
}
