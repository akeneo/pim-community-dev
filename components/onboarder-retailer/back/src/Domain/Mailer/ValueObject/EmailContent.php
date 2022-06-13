<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Retailer\Domain\Mailer\ValueObject;

final class EmailContent
{
    public function __construct(public string $htmlContent, public string $textContent)
    {
    }
}
