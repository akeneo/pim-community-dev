<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Domain;

interface SendEmail
{
    public function __invoke(Email $email): void;
}
