<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Domain\Mailer;

use Akeneo\SupplierPortal\Domain\Mailer\ValueObject\Email;

interface SendEmail
{
    public function __invoke(Email $email): void;
}
