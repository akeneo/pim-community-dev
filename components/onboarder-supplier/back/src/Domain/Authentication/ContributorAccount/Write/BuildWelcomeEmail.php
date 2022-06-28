<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Supplier\Domain\Authentication\ContributorAccount\Write;

use Akeneo\OnboarderSerenity\Supplier\Domain\Mailer\ValueObject\EmailContent;

interface BuildWelcomeEmail
{
    public function __invoke(string $accessToken, string $email): EmailContent;
}
