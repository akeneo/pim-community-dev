<?php

declare(strict_types=1);

namespace AkeneoTest\Platform\Integration\CatalogVolumeMonitoring\Persistence\Query;

use PHPUnit\Framework\Assert;
use AkeneoTest\Platform\Integration\CatalogVolumeMonitoring\Persistence\QueryTestCase;

class CountLocalizableAndScopableAttributesIntegration extends QueryTestCase
{
    public function testGetCountOfLocalizableAndScopableAttributes()
    {
        $query = $this->get('pim_volume_monitoring.persistence.query.count_localizable_and_scopable_attributes');
        $this->createLocalizableAndScopableAttributes(3);
        $this->createScopableAttributes(5);
        $this->createLocalizableAttributes(4);

        $volume = $query->fetch();

        Assert::assertEquals(3, $volume->getVolume());
        Assert::assertEquals('count_localizable_and_scopable_attributes', $volume->getVolumeName());
        Assert::assertEquals(false, $volume->hasWarning());
    }
}
