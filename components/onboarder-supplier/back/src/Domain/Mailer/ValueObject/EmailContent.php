<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Supplier\Domain\Mailer\ValueObject;

final class EmailContent
{
    public function __construct(public string $htmlContent, public string $textContent)
    {
    }
}
