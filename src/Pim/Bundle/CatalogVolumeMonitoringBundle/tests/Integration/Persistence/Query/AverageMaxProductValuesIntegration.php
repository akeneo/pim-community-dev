<?php

declare(strict_types=1);

namespace Pim\Bundle\CatalogVolumeMonitoringBundle\tests\Integration\Persistence\Query;

use PHPUnit\Framework\Assert;
use Pim\Bundle\CatalogVolumeMonitoringBundle\tests\Integration\Persistence\QueryTestCase;

class AverageMaxProductValuesIntegration extends QueryTestCase
{
    public function testGetAverageAndMaximumNumberOfProductValues()
    {
        $query = $this->get('pim_volume_monitoring.persistence.query.average_max_product_values');
        $this->createProductWithProductValues(4);
        $this->createProductWithProductValues(6);

        $volume = $query->fetch();

        Assert::assertEquals(6, $volume->getMaxVolume());
        Assert::assertEquals(5, $volume->getAverageVolume());
        Assert::assertEquals('average_max_product_values', $volume->getVolumeName());
        Assert::assertEquals(false, $volume->hasWarning());
    }

    /**
     * @param int $numberOfProductValues
     */
    private function createProductWithProductValues(int $numberOfProductValues): void
    {
        $family = $this->createFamily(['code' => 'new_family_' . rand()]);
        $arrayProductValues = [];
        $i = 0;

        // -1 because sku is automatically added
        while ($i < $numberOfProductValues -1) {
            $attribute = $this->createAttribute([
                'code'     => 'new_attribute_' . rand(),
                'type'     => 'pim_catalog_text',
                'group'    => 'other'
            ]);

            $family->addAttribute($attribute);
            $arrayProductValues[$attribute->getCode()] = [
                ['data' => rand().' some text random', 'locale' => null, 'scope' => null]
            ];
            $i++;
        }


        $errors = $this->get('validator')->validate($family);
        Assert::assertCount(0, $errors);

        $this->get('pim_catalog.saver.family')->save($family);

        $this->createProduct([
            'identifier' => 'new_product_'.rand(),
            'family' => $family->getCode(),
            'values' => $arrayProductValues
        ]);
    }
}
