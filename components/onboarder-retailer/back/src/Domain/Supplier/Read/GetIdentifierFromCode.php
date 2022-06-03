<?php

namespace Akeneo\OnboarderSerenity\Retailer\Domain\Supplier\Read;

use Akeneo\OnboarderSerenity\Retailer\Domain\Supplier\Write\ValueObject\Code;

interface GetIdentifierFromCode
{
    public function __invoke(Code $code): ?string;
}
