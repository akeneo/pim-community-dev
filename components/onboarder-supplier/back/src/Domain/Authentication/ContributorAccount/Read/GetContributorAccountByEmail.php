<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Supplier\Domain\Authentication\ContributorAccount\Read;

use Akeneo\OnboarderSerenity\Supplier\Domain\Authentication\ContributorAccount\Write\Model\ContributorAccount;

interface GetContributorAccountByEmail
{
    public function __invoke(string $email): ?ContributorAccount;
}
