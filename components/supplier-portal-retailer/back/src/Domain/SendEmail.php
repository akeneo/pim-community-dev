<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Domain;

interface SendEmail
{
    public function __invoke(Email $email): void;
}
