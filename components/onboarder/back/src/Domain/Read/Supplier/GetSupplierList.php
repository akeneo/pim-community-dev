<?php

namespace Akeneo\OnboarderSerenity\Domain\Read\Supplier;

interface GetSupplierList
{
    public const NUMBER_OF_SUPPLIERS_PER_PAGE = 50;

    public function __invoke(int $page = 1, string $search = ''): array;
}
