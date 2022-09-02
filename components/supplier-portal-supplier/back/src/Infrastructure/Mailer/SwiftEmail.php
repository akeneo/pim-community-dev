<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Infrastructure\Mailer;

final class SwiftEmail
{
    public function __construct(
        public string $subject,
        public string $htmlContent,
        public string $txtContent,
        public string $from,
        public string $to,
        /** @var \Swift_Image[] $attachments */
        public array $attachments = [],
    ) {
    }
}
