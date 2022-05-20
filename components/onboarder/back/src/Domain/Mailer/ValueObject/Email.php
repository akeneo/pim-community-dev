<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Domain\Mailer\ValueObject;

final class Email
{
    public function __construct(
        public string $subject,
        public string $HtmlContent,
        public string $txtContent,
        public string $from,
        public string $to
    ){
    }
}
