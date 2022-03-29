<?php

namespace Akeneo\OnboarderSerenity\Domain\Write\Supplier\Contributor;

use Akeneo\OnboarderSerenity\Domain\Write\Supplier\Contributor\Model\Contributor;

interface Repository
{
    public function save(Contributor $contributor): void;
}
