<?php

declare(strict_types=1);

namespace Akeneo\FreeTrial\Infrastructure;

class RetrievePimFQDN
{
    private string $fqdn;

    public function __construct(string $fqdn)
    {
        $this->fqdn = $fqdn;
    }

    public function __invoke(): string
    {
        return $this->fqdn;
    }
}
