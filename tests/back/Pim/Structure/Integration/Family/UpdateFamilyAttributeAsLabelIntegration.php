<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Structure\Integration\Family;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Webmozart\Assert\Assert;

class UpdateFamilyAttributeAsLabelIntegration extends TestCase
{

    public function test_it_updates_only_the_families_where_the_attribute_as_label_have_been_deleted()
    {
        $this->givenAttributes([
            'name', 'title'
        ]);

        $this->givenFamilies([
            [
                'code' => 'accessories',
                'attributes' => [
                    'sku', 'name'
                ],
                'attribute_as_label' => 'name'
            ],
            [
                'code' => 'webcams',
                'attributes' => [
                    'sku', 'title'
                ],
                'attribute_as_label' => 'title'
            ]
        ]);

        $attribute = $this->get('pim_catalog.repository.attribute')->findOneByIdentifier('name');
        $this->get('pim_catalog.remover.attribute')->remove($attribute);
        $this->get('pim_connector.doctrine.cache_clearer')->clear();
        $updatedFamily = $this->get('pim_catalog.repository.family')->findOneByIdentifier('accessories');
        $this->assertSame('sku', $updatedFamily->getAttributeAsLabel()->getCode());

        $notUpdatedFamily = $this->get('pim_catalog.repository.family')->findOneByIdentifier('webcams');
        $this->assertSame('title', $notUpdatedFamily->getAttributeAsLabel()->getCode());
    }

    /**
     * @return Configuration
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function givenAttributes(array $attributeCodes): void
    {
        $attributes = array_map(function ($attributeCode) {
            $attribute = $this->get('pim_catalog.factory.attribute')->create();
            $this->get('pim_catalog.updater.attribute')->update(
                $attribute,
                [
                    'code' => $attributeCode,
                    'type' => 'pim_catalog_text',
                    'group' => 'other'
                ]
            );

            $errors = $this->get('validator')->validate($attribute);
            Assert::count($errors, 0);

            return $attribute;
        }, $attributeCodes);

        $this->get('pim_catalog.saver.attribute')->saveAll($attributes);
    }

    private function givenFamilies(array $families): void
    {
        $families = array_map(function (array $familyData) {
            $family = $this->get('pim_catalog.factory.family')->create();
            $this->get('pim_catalog.updater.family')->update($family, $familyData);
            $constraintViolations = $this->get('validator')->validate($family);

            Assert::count($constraintViolations, 0);

            return $family;
        }, $families);

        $this->get('pim_catalog.saver.family')->saveAll($families);
    }
}
