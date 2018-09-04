<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\CatalogVolumeMonitoringBundle\tests\Integration\Persistence\Query;

use PHPUnit\Framework\Assert;
use Akeneo\Platform\Bundle\CatalogVolumeMonitoringBundle\tests\Integration\Persistence\QueryTestCase;

class CountAttributesIntegration extends QueryTestCase
{
    public function testGetCountOfAttributes()
    {
        $query = $this->get('pim_volume_monitoring.persistence.query.count_attributes');
        $this->createAttributes(8);

        $volume = $query->fetch();

        Assert::assertEquals(8, $volume->getVolume());
        Assert::assertEquals('count_attributes', $volume->getVolumeName());
        Assert::assertEquals(false, $volume->hasWarning());
    }

    /**
     * @param int $numberOfAttributes
     */
    private function createAttributes(int $numberOfAttributes): void
    {
        $i = 0;
        // -1 because sku is automatically added
        while ($i < $numberOfAttributes -1) {
            $this->createAttribute([
                'code'     => 'new_attribute_' . rand(),
                'type'     => 'pim_catalog_text',
                'group'    => 'other'
            ]);
            $i++;
        }
    }
}
