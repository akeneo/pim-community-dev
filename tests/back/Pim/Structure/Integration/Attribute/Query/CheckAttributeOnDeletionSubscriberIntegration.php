<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Structure\Integration\Attribute\Query;

use Akeneo\Pim\Structure\Component\Exception\AttributeRemovalException;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Component\StorageUtils\Remover\BulkRemoverInterface;
use Webmozart\Assert\Assert;

class CheckAttributeOnDeletionSubscriberIntegration extends TestCase
{

    public function test_it_throws_an_exception_when_the_attribute_is_used_as_label_by_a_family()
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
        $this->expectException(AttributeRemovalException::class);
        $this->get('pim_catalog.remover.attribute')->remove($attribute);
        $this->get('pim_connector.doctrine.cache_clearer')->clear();
        $this->assertNotNull($this->get('pim_catalog.repository.attribute')->findOneByIdentifier('name'));
    }

    public function test_it_throws_an_exception_when_the_attributes_are_used_as_label_by_any_family()
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

        $attributeRemover = $this->get('pim_catalog.remover.attribute');
        if (!$attributeRemover instanceof BulkRemoverInterface) {
            $this->markTestSkipped('There is no bulk attribute remover');
        }
        $attributes = $this->get('pim_catalog.repository.attribute')->findBy(['code' => ['name', 'title']]);
        $this->expectException(AttributeRemovalException::class);
        $attributeRemover->removeAll($attributes);
        $this->assertNotNull($this->get('pim_catalog.repository.attribute')->findOneByIdentifier('name'));
        $this->assertNotNull($this->get('pim_catalog.repository.attribute')->findOneByIdentifier('title'));
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
