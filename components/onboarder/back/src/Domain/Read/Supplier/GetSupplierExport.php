<?php

namespace Akeneo\OnboarderSerenity\Domain\Read\Supplier;

interface GetSupplierExport
{
    public function __invoke(): array;
}
