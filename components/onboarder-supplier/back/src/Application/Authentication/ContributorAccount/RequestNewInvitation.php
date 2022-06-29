<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Supplier\Application\Authentication\ContributorAccount;

final class RequestNewInvitation
{
    public function __construct(public string $email)
    {
    }
}
