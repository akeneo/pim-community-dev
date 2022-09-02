<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount;

interface SendGoodbyeEmail
{
    public function __invoke(string $email): void;
}
