<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Domain\Mailer;

use Akeneo\SupplierPortal\Supplier\Domain\Mailer\ValueObject\Email;

interface SendEmail
{
    public function __invoke(Email $email): void;
}
