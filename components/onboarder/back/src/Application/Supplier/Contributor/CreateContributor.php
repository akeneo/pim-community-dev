<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Application\Supplier\Contributor;

final class CreateContributor
{
    public function __construct(public string $email)
    {
    }
}
