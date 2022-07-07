<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount\Event;

final class PasswordReset
{
    public function __construct(public string $contributorAccountEmail, public string $accessToken)
    {
    }
}
