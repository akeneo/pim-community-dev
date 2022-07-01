<?php

namespace Akeneo\OnboarderSerenity\Supplier\Domain\Authentication\ContributorAccount;

use Akeneo\OnboarderSerenity\Supplier\Domain\Mailer\ValueObject\EmailContent;

interface BuildResetPasswordEmail
{
    public function __invoke(string $email, string $accessToken): EmailContent;
}
