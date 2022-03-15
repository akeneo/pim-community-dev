<?php

namespace Akeneo\OnboarderSerenity\Domain\Write\Supplier\Contributor;

use Akeneo\OnboarderSerenity\Domain\Write\Supplier\Model\Supplier;

interface Repository
{
    public function save(Supplier $supplier): void;
}
