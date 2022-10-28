<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Application\Authentication\ContributorAccount\Write\RequestNewInvitation;

final class RequestNewInvitation
{
    public function __construct(public string $email)
    {
    }
}
