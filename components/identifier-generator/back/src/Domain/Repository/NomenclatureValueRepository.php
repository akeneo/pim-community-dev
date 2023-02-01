<?php

namespace Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository;

interface NomenclatureValueRepository
{
    public function update(array $values): void;

    public function get(string $familyCode): ?string;
}
