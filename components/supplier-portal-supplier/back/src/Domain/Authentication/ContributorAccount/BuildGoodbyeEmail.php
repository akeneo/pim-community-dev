<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount;

use Akeneo\SupplierPortal\Supplier\Domain\Mailer\Email;

interface BuildGoodbyeEmail
{
    public function __invoke(string $email): Email;
}
