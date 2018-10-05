<?php

declare(strict_types=1);

namespace AkeneoTest\Platform\Integration\CatalogVolumeMonitoring\Persistence\Query;

use AkeneoTest\Platform\Integration\CatalogVolumeMonitoring\Persistence\QueryTestCase;
use PHPUnit\Framework\Assert;

class AverageMaxScopableAttributesPerFamilyIntegration extends QueryTestCase
{
    public function testGetAverageAndMaximumNumberOfAttributesPerFamily()
    {
        $query = $this->get('pim_volume_monitoring.persistence.query.average_max_scopable_attributes_per_family');
        $this->createFamilyWithScopableAttributes(11, 31);
        $this->createFamilyWithScopableAttributes(0, 8);
        $this->createFamilyWithScopableAttributes(5, 6);

        $volume = $query->fetch();

        Assert::assertEquals(84, $volume->getMaxVolume());
        Assert::assertEquals(40, $volume->getAverageVolume());
        Assert::assertEquals('average_max_scopable_attributes_per_family', $volume->getVolumeName());
        Assert::assertEquals(false, $volume->hasWarning());
    }

    /**
     * @param int $numberOfScopAttributes
     * @param int $numberTotalAttribute
     */
    private function createFamilyWithScopableAttributes(int $numberOfScopAttributes, int $numberTotalAttribute): void
    {
        $family = $this->createFamily([
            'code' => 'family_' . rand()
        ]);

        $i = 0;
        while ($i < $numberOfScopAttributes) {
            $attribute = $this->createAttribute([
                'code'     => 'new_attribute_' . rand(),
                'type'     => 'pim_catalog_textarea',
                'group'    => 'other',
                'localizable' => false,
                'scopable' => true
            ]);

            $family->addAttribute($attribute);
            $i++;
        }

        //attribute sku is automatically added
        while ($i < $numberTotalAttribute-1) {
            $attribute = $this->createAttribute([
                'code'     => 'new_attribute_' . rand(),
                'type'     => 'pim_catalog_textarea',
                'group'    => 'other',
                'localizable' => false,
                'scopable' => false
            ]);

            $family->addAttribute($attribute);
            $i++;
        }

        $errors = $this->get('validator')->validate($family);
        Assert::assertCount(0, $errors);

        $this->get('pim_catalog.saver.family')->save($family);
    }
}
