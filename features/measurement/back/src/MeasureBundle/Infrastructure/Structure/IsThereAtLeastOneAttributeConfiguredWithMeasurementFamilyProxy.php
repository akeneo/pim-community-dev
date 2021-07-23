<?php

declare(strict_types=1);

namespace AkeneoMeasureBundle\Infrastructure\Structure;

use Akeneo\Pim\Structure\Bundle\Query\PublicApi\Attribute\Sql\IsThereAtLeastOneAttributeConfiguredWithMeasurementFamily;

/**
 * We need to create this class as there is no interface for the service "IsThereAtLeastOneAttributeConfiguredWithMeasurementFamily" in the shared|tool component
 */
class IsThereAtLeastOneAttributeConfiguredWithMeasurementFamilyProxy implements IsThereAtLeastOneAttributeConfiguredWithMeasurementFamilyInterface
{
    private IsThereAtLeastOneAttributeConfiguredWithMeasurementFamily $externalService;

    public function __construct(IsThereAtLeastOneAttributeConfiguredWithMeasurementFamily $externalService)
    {
        $this->externalService = $externalService;
    }

    public function execute(string $metricFamilyCode): bool
    {
        return $this->externalService->execute($metricFamilyCode);
    }
}
