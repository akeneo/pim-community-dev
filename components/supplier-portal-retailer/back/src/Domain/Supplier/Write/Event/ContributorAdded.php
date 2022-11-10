<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Event;

use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Model\Supplier\Identifier;

final class ContributorAdded
{
    public function __construct(
        private Identifier $supplierIdentifier,
        private string $contributorEmail,
        private string $supplierCode,
    ) {
    }

    public function supplierIdentifier(): Identifier
    {
        return $this->supplierIdentifier;
    }

    public function contributorEmail(): string
    {
        return $this->contributorEmail;
    }

    public function supplierCode(): string
    {
        return $this->supplierCode;
    }
}
