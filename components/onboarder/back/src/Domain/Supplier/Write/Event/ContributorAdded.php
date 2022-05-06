<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Domain\Supplier\Write\Event;

use Akeneo\OnboarderSerenity\Domain\Supplier\Write\ValueObject\Identifier;
use Symfony\Contracts\EventDispatcher\Event;

final class ContributorAdded extends Event
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
