<?php

declare(strict_types=1);

namespace Pim\Bundle\CatalogVolumeMonitoringBundle\tests\Integration\Persistence\Query;

use PHPUnit\Framework\Assert;
use Pim\Bundle\CatalogVolumeMonitoringBundle\tests\Integration\Persistence\QueryTestCase;

class AverageMaxLocalizableAndScopableAttributesPerFamilyIntegration extends QueryTestCase
{
    public function testGetAverageAndMaximumNumberOfAttributesPerFamily()
    {
        $query = $this->get('pim_volume_monitoring.persistence.query.average_max_localizable_and_scopable_attributes_per_family');
        $this->createFamilyWithAttributes(4, true, true);
        $this->createFamilyWithAttributes(8, true, true);
        $this->createFamilyWithAttributes(5, true, false);
        $this->createFamilyWithAttributes(2, false, true);

        $volume = $query->fetch();

        Assert::assertEquals(8, $volume->getMaxVolume());
        Assert::assertEquals(6, $volume->getAverageVolume());
        Assert::assertEquals('average_max_localizable_and_scopable_attributes_per_family', $volume->getVolumeName());
        Assert::assertEquals(false, $volume->hasWarning());
    }

    /**
     * @param int $numberOfAttributes
     * @param bool $localizable
     * @param bool $scopable
     */
    private function createFamilyWithAttributes(int $numberOfAttributes, bool $localizable, bool $scopable): void
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
                'localizable' => $localizable,
                'scopable' => $scopable
            ]);

            $family->addAttribute($attribute);
            $i++;
        }
        $errors = $this->get('validator')->validate($family);
        Assert::assertCount(0, $errors);

        $this->get('pim_catalog.saver.family')->save($family);
    }
}
