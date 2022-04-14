<?php

namespace Akeneo\OnboarderSerenity\Infrastructure\Supplier\Encoder;

interface SuppliersEncoder
{
    public function __invoke(array $suppliersWithContributors): string;
}
