<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Supplier\Domain\Authentication\ContributorAccount\Write\Event;

final class ResetPasswordRequested
{
    public function __construct(public string $contributorAccountEmail, public string $accessToken)
    {
    }
}
