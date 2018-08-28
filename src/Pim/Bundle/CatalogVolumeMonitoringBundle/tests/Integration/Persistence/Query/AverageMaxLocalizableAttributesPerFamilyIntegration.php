<?php

declare(strict_types=1);

namespace Pim\Bundle\CatalogVolumeMonitoringBundle\tests\Integration\Persistence\Query;

use PHPUnit\Framework\Assert;
use Pim\Bundle\CatalogVolumeMonitoringBundle\tests\Integration\Persistence\QueryTestCase;

class AverageMaxLocalizableAttributesPerFamilyIntegration extends QueryTestCase
{
    public function testGetAverageAndMaximumNumberOfAttributesPerFamily()
    {
        $query = $this->get('pim_volume_monitoring.persistence.query.average_max_localizable_attributes_per_family');
        $this->createFamilyWithAttributes(4, true);
        $this->createFamilyWithAttributes(8, true);
        $this->createFamilyWithAttributes(2, false);

        $volume = $query->fetch();

        Assert::assertEquals(8, $volume->getMaxVolume());
        Assert::assertEquals(6, $volume->getAverageVolume());
        Assert::assertEquals('average_max_localizable_attributes_per_family', $volume->getVolumeName());
        Assert::assertEquals(false, $volume->hasWarning());
    }

    /**
     * @param int $numberOfAttributes
     * @param bool $localizable
     */
    private function createFamilyWithAttributes(int $numberOfAttributes, bool $localizable): void
    {
        $family = $this->createFamily([
            'code' => 'family_' . rand()
        ]);

        $i = 0;
        while ($i < $numberOfAttributes) {
            $attribute = $this->createAttribute([
                'code'     => 'new_attribute_' . rand(),
                'type'     => 'pim_catalog_textarea',
                'group'    => 'other',
                'localizable' => $localizable
            ]);

            $family->addAttribute($attribute);
            $i++;
        }
        $errors = $this->get('validator')->validate($family);
        Assert::assertCount(0, $errors);

        $this->get('pim_catalog.saver.family')->save($family);
    }
}
