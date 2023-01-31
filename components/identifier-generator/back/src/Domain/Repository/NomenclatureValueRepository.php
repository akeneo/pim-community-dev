<?php

namespace Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository;

interface NomenclatureValueRepository
{
    public function set(string $familyCode, ?string $value): void;

    public function get(string $familyCode): ?string;
}
