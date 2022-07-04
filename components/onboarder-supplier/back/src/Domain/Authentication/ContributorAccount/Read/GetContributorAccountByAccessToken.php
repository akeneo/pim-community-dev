<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Domain\Authentication\ContributorAccount\Read;

use Akeneo\SupplierPortal\Domain\Authentication\ContributorAccount\Read\Model\ContributorAccount;

interface GetContributorAccountByAccessToken
{
    public function __invoke(string $accessToken): ?ContributorAccount;
}
