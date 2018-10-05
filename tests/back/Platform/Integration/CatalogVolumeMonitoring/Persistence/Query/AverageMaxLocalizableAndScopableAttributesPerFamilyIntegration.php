<?php

declare(strict_types=1);

namespace AkeneoTest\Platform\Integration\CatalogVolumeMonitoring\Persistence\Query;

use PHPUnit\Framework\Assert;
use AkeneoTest\Platform\Integration\CatalogVolumeMonitoring\Persistence\QueryTestCase;

class AverageMaxLocalizableAndScopableAttributesPerFamilyIntegration extends QueryTestCase
{
    public function testGetAverageAndMaximumNumberOfAttributesPerFamily()
    {
        $query = $this->get('pim_volume_monitoring.persistence.query.average_max_localizable_and_scopable_attributes_per_family');
        $this->createFamilyWithLocalizableAndScopableAttributes(12, 30);
        $this->createFamilyWithLocalizableAndScopableAttributes(0, 10);
        $this->createFamilyWithLocalizableAndScopableAttributes(8, 10);

        $volume = $query->fetch();

        Assert::assertEquals(80, $volume->getMaxVolume());
        Assert::assertEquals(40, $volume->getAverageVolume());
        Assert::assertEquals('average_max_localizable_and_scopable_attributes_per_family', $volume->getVolumeName());
        Assert::assertEquals(false, $volume->hasWarning());
    }

    /**
     * @param int $numberOfLocScopAttributes
     * @param int $numberTotalAttribute
     */
    private function createFamilyWithLocalizableAndScopableAttributes(
        int $numberOfLocScopAttributes,
        int $numberTotalAttribute
    ): void{
        $family = $this->createFamily([
            'code' => 'family_' . rand()
        ]);

        $i = 0;
        while ($i < $numberOfLocScopAttributes) {
            $attribute = $this->createAttribute([
                'code'     => 'new_attribute_' . rand(),
                'type'     => 'pim_catalog_textarea',
                'group'    => 'other',
                'localizable' => true,
                'scopable' => true
            ]);

            $family->addAttribute($attribute);
            $i++;
        }

        //attribute sku is automatically added
        while ($i < $numberTotalAttribute - 1) {
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
