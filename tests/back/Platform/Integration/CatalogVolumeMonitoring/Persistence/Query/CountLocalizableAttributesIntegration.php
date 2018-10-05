<?php

declare(strict_types=1);

namespace AkeneoTest\Platform\Integration\CatalogVolumeMonitoring\Persistence\Query;

use PHPUnit\Framework\Assert;
use AkeneoTest\Platform\Integration\CatalogVolumeMonitoring\Persistence\QueryTestCase;

class CountLocalizableAttributesIntegration extends QueryTestCase
{
    public function testGetCountOfLocalizableAttributes()
    {
        $query = $this->get('pim_volume_monitoring.persistence.query.count_localizable_attributes');
        $this->createLocalizableAndScopableAttributes(3);
        $this->createScopableAttributes(5);
        $this->createLocalizableAttributes(4);

        $volume = $query->fetch();

        Assert::assertEquals(4, $volume->getVolume());
        Assert::assertEquals('count_localizable_attributes', $volume->getVolumeName());
        Assert::assertEquals(false, $volume->hasWarning());
    }
}
