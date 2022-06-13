<?php

namespace Akeneo\OnboarderSerenity\Retailer\Domain\Supplier\Write;

use Akeneo\OnboarderSerenity\Retailer\Domain\Supplier\Write\Model\Supplier;
use Akeneo\OnboarderSerenity\Retailer\Domain\Supplier\Write\ValueObject\Identifier;

interface Repository
{
    public function save(Supplier $supplier): void;
    public function delete(Identifier $identifier): void;
    public function find(Identifier $identifier): ?Supplier;
}
