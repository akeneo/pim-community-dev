<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Domain;

final class Email
{
    public function __construct(
        public string $subject,
        public string $htmlTemplate,
        public string $textTemplate,
        /** @var array<string, mixed> $templateContext */
        public array $templateContext,
        public string $to,
        public string $embeddedLogoPath,
    ) {
    }
}
