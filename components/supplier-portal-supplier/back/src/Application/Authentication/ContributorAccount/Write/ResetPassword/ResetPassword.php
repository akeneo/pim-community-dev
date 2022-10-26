<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Application\Authentication\ContributorAccount\Write\ResetPassword;

final class ResetPassword
{
    public function __construct(public string $email)
    {
    }
}
