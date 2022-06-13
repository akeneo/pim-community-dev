<?php

namespace Akeneo\OnboarderSerenity\Retailer\Infrastructure\Supplier\Encoder;

interface SuppliersEncoder
{
    public function __invoke(array $suppliersWithContributors): string;
}
