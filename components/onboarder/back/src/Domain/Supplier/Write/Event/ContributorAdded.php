<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Domain\Supplier\Write\Event;

use Akeneo\OnboarderSerenity\Domain\Supplier\Write\ValueObject\Identifier;

final class ContributorAdded
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
