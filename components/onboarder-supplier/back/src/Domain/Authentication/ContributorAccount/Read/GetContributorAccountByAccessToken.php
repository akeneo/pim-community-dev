<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Supplier\Domain\Authentication\ContributorAccount\Read;

use Akeneo\OnboarderSerenity\Supplier\Domain\Authentication\ContributorAccount\Read\Model\ContributorAccount;

interface GetContributorAccountByAccessToken
{
    public function __invoke(string $accessToken): ?ContributorAccount;
}
