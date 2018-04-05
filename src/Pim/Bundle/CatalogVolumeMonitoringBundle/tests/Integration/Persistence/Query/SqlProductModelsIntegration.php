<?php

declare(strict_types=1);

namespace Pim\Bundle\CatalogVolumeMonitoringBundle\tests\Integration\Persistence\Query;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;
use Pim\Bundle\CatalogVolumeMonitoringBundle\tests\Integration\Persistence\BuilderQueryTestCase;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Model\FamilyVariantInterface;

class SqlProductModelsIntegration extends BuilderQueryTestCase
{
    public function testGetCountOfProductModels()
    {
        $query = $this->get('pim_volume_monitoring.persistence.query.product_models');
        $this->createProductModels(4);

        $volume = $query->fetch();

        Assert::assertEquals(4, $volume->getVolume());
        Assert::assertEquals('product_models', $volume->getVolumeName());
        Assert::assertEquals(false, $volume->hasWarning());
    }

    /**
     * @param int $numberOfProductModels
     */
    private function createProductModels(int $numberOfProductModels): void
    {
        $attribute = $this->createAttribute([
            'code'     => 'new_attribute_' . rand(),
            'type'     => 'pim_catalog_boolean',
            'group'    => 'other'
        ]);
        $family = $this->createFamily(['code' => 'new_family_' . rand()]);

        $family->addAttribute($attribute);
        $errors = $this->get('validator')->validate($family);
        Assert::assertCount(0, $errors);

        $this->get('pim_catalog.saver.family')->save($family);

        $familyVariant = $this->createFamilyVariant([
            'code'     => 'new_family_variant_' . rand(),
            'variant_attribute_sets' => [
                [
                    'axes' => [$attribute->getCode()],
                    'attributes' => [],
                    'level'=> 1,
                ]
            ],
            'family' => $family->getCode()
        ]);

        $i = 0;
        while ($i < $numberOfProductModels) {
            $this->createProductModel([
                'code'           => 'new_product_model_' . rand(),
                'family_variant' => $familyVariant->getCode()
            ]);

            $i++;
        }
    }
}
