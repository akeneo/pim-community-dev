<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Retailer\Domain\Authentication\ContributorAccount\Write;

use Akeneo\OnboarderSerenity\Retailer\Domain\Mailer\ValueObject\EmailContent;

interface BuildWelcomeEmail
{
    public function __invoke(string $accessToken, string $email): EmailContent;
}
