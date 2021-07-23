<?php

declare(strict_types=1);

namespace AkeneoMeasureBundle\Infrastructure\Structure;

class InMemoryIsThereAtLeastOneAttributeConfiguredWithMeasurementFamily implements IsThereAtLeastOneAttributeConfiguredWithMeasurementFamilyInterface
{
    public function execute(string $metricFamilyCode): bool
    {
        if ('Length' === $metricFamilyCode) return false;

        return true;
    }
}
