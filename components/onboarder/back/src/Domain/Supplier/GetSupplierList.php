<?php

namespace Akeneo\OnboarderSerenity\Domain\Supplier;

interface GetSupplierList
{
    public function __invoke(int $page = 1, string $search = ''): array;
}
