<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Domain\Mailer;

use Akeneo\OnboarderSerenity\Domain\Mailer\ValueObject\Email;

interface SendEmail
{
    public function __invoke(Email $email): void;
}
