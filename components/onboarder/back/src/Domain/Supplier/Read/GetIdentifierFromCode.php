<?php

namespace Akeneo\OnboarderSerenity\Domain\Supplier\Read;

use Akeneo\OnboarderSerenity\Domain\Supplier\Write\ValueObject\Code;

interface GetIdentifierFromCode
{
    public function __invoke(Code $code): ?string;
}
