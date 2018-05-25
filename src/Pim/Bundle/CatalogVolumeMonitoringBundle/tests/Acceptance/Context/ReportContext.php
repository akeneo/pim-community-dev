<?php

declare(strict_types=1);

namespace Pim\Bundle\CatalogVolumeMonitoringBundle\tests\Acceptance\Context;

use Behat\Behat\Context\Context;
use Pim\Component\CatalogVolumeMonitoring\Volume\Normalizer;

final class ReportContext implements Context
{
    /** @var array */
    private $volumes = [];

    /** @var Normalizer\Volumes */
    private $volumesNormalizer;

    /**
     * @param Normalizer\Volumes $volumesNormalizer
     */
    public function __construct(Normalizer\Volumes $volumesNormalizer)
    {
        $this->volumesNormalizer = $volumesNormalizer;
    }

    /**
     * @When the administrator user asks for the catalog volume monitoring report
     */
    public function theAdministratorUserAsksForTheCatalogVolumeMonitoringReport(): void
    {
        $this->volumes = $this->volumesNormalizer->volumes();
    }

    /**
     * @return array
     */
    public function getVolumes(): array
    {
        return $this->volumes;
    }
}
