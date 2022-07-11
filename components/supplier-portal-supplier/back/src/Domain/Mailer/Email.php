<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Domain\Mailer;

final class Email
{
    public function __construct(
        public string $subject,
        public string $htmlContent,
        public string $txtContent,
        public string $from,
        public string $to,
    ) {
    }
}
