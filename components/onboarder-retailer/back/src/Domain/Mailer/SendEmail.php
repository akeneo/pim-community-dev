<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Retailer\Domain\Mailer;

use Akeneo\OnboarderSerenity\Retailer\Domain\Mailer\ValueObject\Email;

interface SendEmail
{
    public function __invoke(Email $email): void;
}
