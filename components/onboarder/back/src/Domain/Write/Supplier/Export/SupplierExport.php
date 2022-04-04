<?php

namespace Akeneo\OnboarderSerenity\Domain\Write\Supplier\Export;

interface SupplierExport
{
    public function __invoke(): string;
}
