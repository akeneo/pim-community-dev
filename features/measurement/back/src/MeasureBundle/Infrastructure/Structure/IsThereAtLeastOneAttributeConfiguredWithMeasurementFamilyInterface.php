<?php

declare(strict_types=1);

namespace AkeneoMeasureBundle\Infrastructure\Structure;

interface IsThereAtLeastOneAttributeConfiguredWithMeasurementFamilyInterface
{
    public function execute(string $metricFamilyCode): bool;
}
