<?php

declare(strict_types=1);

namespace AkeneoTest\Platform\Integration\CatalogVolumeMonitoring\Persistence\Query;

use PHPUnit\Framework\Assert;
use AkeneoTest\Platform\Integration\CatalogVolumeMonitoring\Persistence\QueryTestCase;

class CountVariantProductsIntegration extends QueryTestCase
{
    public function testGetCountOfProducts()
    {
        $query = $this->get('pim_volume_monitoring.persistence.query.count_variant_products');
        $this->createVariantProducts(8);

        $volume = $query->fetch();

        Assert::assertEquals(8, $volume->getVolume());
        Assert::assertEquals('count_variant_products', $volume->getVolumeName());
        Assert::assertEquals(false, $volume->hasWarning());
    }

    private function createVariantProducts(int $numberOfVariantProducts) : void
    {
        $attribute = $this->createAttribute([
            'code'     => 'new_attribute_' . rand(),
            'type'     => 'pim_catalog_simpleselect',
            'group'    => 'other'
        ]);

        $options = [];
        $i = 0;
        while ($i < $numberOfVariantProducts) {
            $options[] = $this->createAttributeOption([
                'attribute' => $attribute->getCode(),
                'code' => 'new_option_' . rand()
            ]);
            $i++;
        }

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

        $productModel = $this->createProductModel([
            'code'           => 'new_product_model_' . rand(),
            'family_variant' => $familyVariant->getCode()
        ]);

        $i = 0;
        while ($i < $numberOfVariantProducts) {
            $this->createVariantProduct('new_variant_product_' . rand(), [
                'categories' => ['master'],
                'parent' => $productModel->getCode(),
                'values' => [
                    $attribute->getCode() => [
                        [
                            'locale' => null,
                            'scope' => null,
                            'data' => $options[$i]->getCode(),
                        ],
                    ],
                ]
            ]);

            $i++;
        }
    }
}
