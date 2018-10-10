<?php

declare(strict_types=1);

namespace AkeneoTest\Platform\Integration\CatalogVolumeMonitoring\Persistence\Query;

use AkeneoTest\Platform\Integration\CatalogVolumeMonitoring\Persistence\QueryTestCase;
use PHPUnit\Framework\Assert;

class AverageMaxAttributesPerFamilyIntegration extends QueryTestCase
{
    public function testGetAverageAndMaximumNumberOfAttributesPerFamily()
    {
        $query = $this->get('pim_volume_monitoring.persistence.query.average_max_attributes_per_family');
        $this->createFamilyWithAttributes(4);
        $this->createFamilyWithAttributes(8);
        $this->createFamilyWithAttributes(2);

        $volume = $query->fetch();

        Assert::assertEquals(8, $volume->getMaxVolume());
        Assert::assertEquals(5, $volume->getAverageVolume());
        Assert::assertEquals('average_max_attributes_per_family', $volume->getVolumeName());
        Assert::assertEquals(false, $volume->hasWarning());
    }

    /**
     * @param int $numberOfAttributes
     */
    private function createFamilyWithAttributes(int $numberOfAttributes): void
    {
        $family = $this->createFamily([
            'code' => 'family_' . rand()
        ]);

        $i = 0;
        // -1 because sku is automatically added
        while ($i < $numberOfAttributes -1) {
            $attribute = $this->createAttribute([
                'code'     => 'new_attribute_' . rand(),
                'type'     => 'pim_catalog_textarea',
                'group'    => 'other'
            ]);

            $family->addAttribute($attribute);
            $i++;
        }
        $errors = $this->get('validator')->validate($family);
        Assert::assertCount(0, $errors);

        $this->get('pim_catalog.saver.family')->save($family);
    }
}
