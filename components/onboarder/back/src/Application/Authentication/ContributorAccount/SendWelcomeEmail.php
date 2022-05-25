<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Application\Authentication\ContributorAccount;

final class SendWelcomeEmail
{
    public function __construct(public string $accessToken, public string $email)
    {
    }
}
