<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Application\Authentication\ContributorAccount;

use Akeneo\OnboarderSerenity\Domain\Authentication\ContributorAccount\Write\Model\ContributorAccount;

final class SendWelcomeEmail
{
    public function __construct(public ContributorAccount $contributorAccount)
    {
    }
}
