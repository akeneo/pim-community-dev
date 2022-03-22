<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Domain\Read\Supplier\Model;

final class Contributor
{
    public function __construct(
        public string $identifier,
        public string $email,
    ) {
    }

    public function toArray(): array
    {
        return [
            'identifier' => $this->identifier,
            'email' => $this->email,
        ];
    }
}
