<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Supplier\Domain\Mailer;

use Akeneo\OnboarderSerenity\Supplier\Domain\Mailer\ValueObject\Email;

interface SendEmail
{
    public function __invoke(Email $email): void;
}
