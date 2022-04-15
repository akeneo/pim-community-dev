<?php

namespace Akeneo\OnboarderSerenity\Domain\Supplier\Write;

use Akeneo\OnboarderSerenity\Domain\Supplier\Write\Model\Supplier;
use Akeneo\OnboarderSerenity\Domain\Supplier\Write\ValueObject\Identifier;

interface Repository
{
    public function save(Supplier $supplier): void;
    public function delete(Identifier $identifier): void;
    public function find(Identifier $identifier): ?Supplier;
}
