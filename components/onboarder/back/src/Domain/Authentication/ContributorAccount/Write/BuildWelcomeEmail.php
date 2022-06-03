<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Domain\Authentication\ContributorAccount\Write;

use Akeneo\OnboarderSerenity\Domain\Mailer\ValueObject\EmailContent;

interface BuildWelcomeEmail
{
    public function __invoke(string $accessToken, string $email): EmailContent;
}
