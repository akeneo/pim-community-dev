<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Domain\Mailer;

interface SendEmail
{
    public function __invoke(Email $email): void;
}
