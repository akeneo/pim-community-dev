<?php

declare(strict_types=1);

namespace AkeneoTest\Platform\Integration\CatalogVolumeMonitoring\Persistence\Query;

use PHPUnit\Framework\Assert;
use AkeneoTest\Platform\Integration\CatalogVolumeMonitoring\Persistence\QueryTestCase;

class CountScopableAttributesIntegration extends QueryTestCase
{
    public function testGetCountOfScopableAttributes()
    {
        $query = $this->get('pim_volume_monitoring.persistence.query.count_scopable_attributes');
        $this->createLocalizableAndScopableAttributes(3);
        $this->createScopableAttributes(5);
        $this->createLocalizableAttributes(4);

        $volume = $query->fetch();

        Assert::assertEquals(5, $volume->getVolume());
        Assert::assertEquals('count_scopable_attributes', $volume->getVolumeName());
        Assert::assertEquals(false, $volume->hasWarning());
    }
}
