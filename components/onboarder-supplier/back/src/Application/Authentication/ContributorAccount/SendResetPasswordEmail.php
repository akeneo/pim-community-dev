<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Application\Authentication\ContributorAccount;

final class SendResetPasswordEmail
{
    public function __construct(public string $email, public string $accessToken)
    {
    }
}
