<?php

declare(strict_types=1);

namespace Pim\Bundle\CatalogVolumeMonitoringBundle\tests\Integration\Persistence\Query;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

class SqlAttributesPerFamilyIntegration extends TestCase
{
    public function testGetAverageAndMaximumNumberOfAttributesPerFamily()
    {
        $query = $this->get('pim_volume_monitoring.persistence.query.attributes_per_family');
        $this->createFamilyWithAttributes(4);
        $this->createFamilyWithAttributes(8);
        $this->createFamilyWithAttributes(2);

        $volume = $query->fetch();

        Assert::assertEquals(8, $volume->getMaxVolume());
        Assert::assertEquals(5, $volume->getAverageVolume());
        Assert::assertEquals('attributes_per_family', $volume->getVolumeName());
        Assert::assertEquals(false, $volume->hasWarning());
    }

    /**
     * @param int $numberOfAttributes
     */
    private function createFamilyWithAttributes(int $numberOfAttributes): void
    {
        $family = $this->get('pim_catalog.factory.family')->create();
        $family->setCode('family_' . rand());

        $attributes = [];
        $i = 0;
        // -1 because sku is automatically added
        while ($i < $numberOfAttributes -1) {
            $attribute = $this->get('pim_catalog.factory.attribute')->create();
            $this->get('pim_catalog.updater.attribute')->update(
                $attribute,
                [
                    'code'     => 'new_attribute_' . rand(),
                    'type'     => 'pim_catalog_textarea',
                    'group'    => 'other'
                ]
            );

            $errors = $this->get('validator')->validate($attribute);
            Assert::assertCount(0, $errors);

            $family->addAttribute($attribute);
            $attributes[] = $attribute;

            $i++;
        }

        $this->get('pim_catalog.saver.attribute')->saveAll($attributes);

        $errors = $this->get('validator')->validate($family);
        Assert::assertCount(0, $errors);

        $this->get('pim_catalog.saver.family')->save($family);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
