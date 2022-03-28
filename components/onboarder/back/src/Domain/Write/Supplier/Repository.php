<?php

namespace Akeneo\OnboarderSerenity\Domain\Write\Supplier;

use Akeneo\OnboarderSerenity\Domain\Write\Supplier\Model\Supplier;
use Akeneo\OnboarderSerenity\Domain\Write\Supplier\ValueObject\Identifier;

interface Repository
{
    public function save(Supplier $supplier): void;
    public function delete(Identifier $identifier): void;
    public function getByIdentifier(Identifier $identifier): ?Supplier;
}
