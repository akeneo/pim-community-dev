<?php

declare(strict_types=1);

namespace AkeneoTest\Platform\Integration\CatalogVolumeMonitoring\Persistence\Query;

use PHPUnit\Framework\Assert;
use AkeneoTest\Platform\Integration\CatalogVolumeMonitoring\Persistence\QueryTestCase;

class CountUseableAsGridFilterAttributesIntegration extends QueryTestCase
{
    public function testGetCountOfScopableAttributes()
    {
        $query = $this->get('pim_volume_monitoring.persistence.query.count_useable_as_grid_filter_attributes');
        $this->createUseableAsGridFilterAttributes(5);

        $volume = $query->fetch();

        Assert::assertEquals(5, $volume->getVolume());
        Assert::assertEquals('count_useable_as_grid_filter_attributes', $volume->getVolumeName());
        Assert::assertEquals(false, $volume->hasWarning());
    }

    /**
     * @param int $numberOfAttributes
     */
    protected function createUseableAsGridFilterAttributes(int $numberOfAttributes)
    {
        $i = 0;
        // -1 because sku is automatically added (as filter)
        while ($i < $numberOfAttributes -1) {
            $this->createAttribute([
                'code'     => 'new_attribute_' . rand(),
                'type'     => 'pim_catalog_text',
                'group'    => 'other',
                'localizable' => false,
                'scopable' => false,
                'useable_as_grid_filter' => true
            ]);
            $i++;
        }
    }
}
