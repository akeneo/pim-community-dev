<?php

declare(strict_types=1);

namespace AkeneoTest\Platform\Integration\CatalogVolumeMonitoring\Persistence\Query;

use PHPUnit\Framework\Assert;
use AkeneoTest\Platform\Integration\CatalogVolumeMonitoring\Persistence\QueryTestCase;
use Akeneo\Pim\Structure\Component\AttributeTypes;

class AverageMaxOptionsPerAttributeIntegration extends QueryTestCase
{
    public function testGetAverageAndMaximumNumberOfOptionsPerAttribute()
    {
        $query = $this->get('pim_volume_monitoring.persistence.query.average_max_options_per_attribute');
        $this->createAttributeWithOptions(4, AttributeTypes::OPTION_SIMPLE_SELECT);
        $this->createAttributeWithOptions(8, AttributeTypes::OPTION_MULTI_SELECT);
        $this->createAttributeWithOptions(2, AttributeTypes::OPTION_SIMPLE_SELECT);

        $volume = $query->fetch();

        Assert::assertEquals(8, $volume->getMaxVolume());
        Assert::assertEquals(5, $volume->getAverageVolume());
        Assert::assertEquals('average_max_options_per_attribute', $volume->getVolumeName());
        Assert::assertEquals(false, $volume->hasWarning());
    }

    /**
     * @param int $numberOfOptions
     * @param string $typeOfAttribute
     */
    private function createAttributeWithOptions(int $numberOfOptions, string $typeOfAttribute): void
    {
        $attribute = $this->createAttribute([
            'code'     => 'new_attribute_' . rand(),
            'type'     => $typeOfAttribute,
            'group'    => 'other'
        ]);

        $i = 0;
        while ($i < $numberOfOptions) {
            $this->createAttributeOption([
                'code' => 'option_' . rand(),
                'attribute' => $attribute->getCode()
            ]);
            $i++;
        }
    }
}
