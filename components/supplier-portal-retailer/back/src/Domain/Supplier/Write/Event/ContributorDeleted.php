<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Event;

use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Model\Supplier\Identifier;

final class ContributorDeleted
{
    public function __construct(private Identifier $supplierIdentifier, private string $contributorEmail)
    {
    }

    public function supplierIdentifier(): Identifier
    {
        return $this->supplierIdentifier;
    }

    public function contributorEmail(): string
    {
        return $this->contributorEmail;
    }
}
