<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Retailer\Domain\Supplier\Write\Event;

use Akeneo\OnboarderSerenity\Retailer\Domain\Supplier\Write\ValueObject\Identifier;

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
