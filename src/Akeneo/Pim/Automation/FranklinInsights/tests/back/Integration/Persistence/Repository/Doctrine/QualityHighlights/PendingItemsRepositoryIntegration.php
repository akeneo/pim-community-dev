<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\FranklinInsights\tests\back\Integration\Persistence\Repository\Doctrine\QualityHighlights;

use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\Repository\PendingItemsRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\ValueObject\Lock;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Persistence\Repository\Doctrine\QualityHighlights\PendingItemsRepository;
use Akeneo\Pim\Enrichment\Component\Product\Builder\EntityWithValuesBuilderInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeGroup;
use Akeneo\Test\Common\EntityBuilder;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Doctrine\DBAL\Connection;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PendingItemsRepositoryIntegration extends TestCase
{
    /** @var EntityBuilder */
    private $attributeBuilder;

    /** @var SaverInterface */
    private $attributeSaver;

    /** @var ValidatorInterface */
    private $validator;

    /** @var EntityBuilder */
    private $familyBuilder;

    /** @var SaverInterface */
    private $familySaver;

    /** @var EntityWithValuesBuilderInterface */
    private $productBuilder;

    /**@var SaverInterface */
    private $productSaver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->attributeBuilder = $this->getFromTestContainer('akeneo_ee_integration_tests.builder.attribute');
        $this->attributeSaver = $this->getFromTestContainer('pim_catalog.saver.attribute');
        $this->familyBuilder = $this->getFromTestContainer('akeneo_ee_integration_tests.builder.family');
        $this->familySaver = $this->getFromTestContainer('pim_catalog.saver.family');
        $this->productBuilder = $this->getFromTestContainer('akeneo_integration_tests.catalog.product.builder');
        $this->productSaver = $this->getFromTestContainer('pim_catalog.saver.product');
        $this->validator = $this->getFromTestContainer('validator');
    }

    public function test_it_saves_an_updated_attribute_code(): void
    {
        $sqlQuery = 'SELECT * FROM pimee_franklin_insights_quality_highlights_pending_items';
        $updatedAttributes = $this->getDbConnection()->query($sqlQuery)->fetchAll();
        $this->assertCount(0, $updatedAttributes);

        $this->getRepository()->addUpdatedAttributeCode('weight');
        $sqlQuery = 'SELECT entity_type, entity_id, action, lock_id FROM pimee_franklin_insights_quality_highlights_pending_items';
        $updatedAttributes = $this->getDbConnection()->query($sqlQuery)->fetchAll();
        $this->assertCount(1, $updatedAttributes);
        $this->assertSame(
            [
                'entity_type' => PendingItemsRepository::ENTITY_TYPE_ATTRIBUTE,
                'entity_id' => 'weight',
                'action' => PendingItemsRepository::ACTION_ENTITY_UPDATED,
                'lock_id' => '',
            ],
            $updatedAttributes[0]
        );
    }

    public function test_it_updates_the_action_on_duplicated_entry()
    {
        $sqlQuery = <<<SQL
INSERT INTO pimee_franklin_insights_quality_highlights_pending_items(entity_type, entity_id, action)
VALUES (:entity_type, :entity_id, :action)
SQL;
        $bindParams = [
            'entity_type' => PendingItemsRepository::ENTITY_TYPE_ATTRIBUTE,
            'entity_id' => 'size',
            'action' => PendingItemsRepository::ACTION_ENTITY_DELETED,
            'lock_id' => '',
        ];
        $this->getDbConnection()->executeQuery($sqlQuery, $bindParams);

        $this->getRepository()->addUpdatedAttributeCode('size');
        $sqlQuery = 'SELECT entity_type, entity_id, action, lock_id FROM pimee_franklin_insights_quality_highlights_pending_items';
        $updatedAttributes = $this->getDbConnection()->query($sqlQuery)->fetchAll();
        $this->assertCount(1, $updatedAttributes);
        $this->assertSame(
            [
                'entity_type' => PendingItemsRepository::ENTITY_TYPE_ATTRIBUTE,
                'entity_id' => 'size',
                'action' => PendingItemsRepository::ACTION_ENTITY_UPDATED,
                'lock_id' => '',
            ],
            $updatedAttributes[0]
        );
    }

    public function test_it_saves_an_updated_product_id(): void
    {
        $sqlQuery = 'SELECT * FROM pimee_franklin_insights_quality_highlights_pending_items';
        $updatedProducts = $this->getDbConnection()->query($sqlQuery)->fetchAll();
        $this->assertCount(0, $updatedProducts);

        $this->getRepository()->addUpdatedProductId(42);
        $sqlQuery = 'SELECT entity_type, entity_id, action, lock_id FROM pimee_franklin_insights_quality_highlights_pending_items';
        $updatedProducts = $this->getDbConnection()->query($sqlQuery)->fetchAll();
        $this->assertCount(1, $updatedProducts);
        $this->assertSame(
            [
                'entity_type' => PendingItemsRepository::ENTITY_TYPE_PRODUCT,
                'entity_id' => '42',
                'action' => PendingItemsRepository::ACTION_ENTITY_UPDATED,
                'lock_id' => '',
            ],
            $updatedProducts[0]
        );
    }

    public function test_it_initializes_with_all_attributes_code(): void
    {
        $sqlQuery = 'SELECT entity_id FROM pimee_franklin_insights_quality_highlights_pending_items';
        $updatedAttributes = $this->getDbConnection()->query($sqlQuery)->fetchAll();
        $this->assertCount(0, $updatedAttributes);

        $this->createTextAttribute('size');
        $this->createTextAttribute('weight');
        $this->createIdentifierAttribute('unauthorized-type');

        $this->getRepository()->fillWithAllAttributes();

        $sqlQuery = 'SELECT entity_id FROM pimee_franklin_insights_quality_highlights_pending_items WHERE entity_type=:entity_type';
        $updatedAttributes = $this->getDbConnection()->executeQuery($sqlQuery, ['entity_type' => PendingItemsRepository::ENTITY_TYPE_ATTRIBUTE])->fetchAll();

        //Only authorized attributes
        $this->assertCount(2, $updatedAttributes);
        $this->assertSame(['size', 'weight'], array_column($updatedAttributes, 'entity_id'));
    }

    public function test_it_initializes_with_all_families_code(): void
    {
        $sqlQuery = 'SELECT entity_id FROM pimee_franklin_insights_quality_highlights_pending_items';
        $updatedFamilies = $this->getDbConnection()->query($sqlQuery)->fetchAll();
        $this->assertCount(0, $updatedFamilies);

        $this->createFamily('laptop');
        $this->createFamily('tv');

        $this->getRepository()->fillWithAllFamilies();

        $sqlQuery = 'SELECT entity_id FROM pimee_franklin_insights_quality_highlights_pending_items WHERE entity_type=:entity_type';
        $updatedFamilies = $this->getDbConnection()->executeQuery($sqlQuery, ['entity_type' => PendingItemsRepository::ENTITY_TYPE_FAMILY])->fetchAll();
        $this->assertCount(2, $updatedFamilies);
        $this->assertSame(['laptop', 'tv'], array_column($updatedFamilies, 'entity_id'));
    }

    public function test_it_initializes_with_all_products_code(): void
    {
        $sqlQuery = 'SELECT entity_id FROM pimee_franklin_insights_quality_highlights_pending_items';
        $updatedFamilies = $this->getDbConnection()->query($sqlQuery)->fetchAll();
        $this->assertCount(0, $updatedFamilies);

        $this->createProduct('productA');
        $this->createProduct('productB');
        $this->createProduct('disableProduct', false);

        $this->getRepository()->fillWithAllProducts();

        $sqlQuery = 'SELECT entity_id FROM pimee_franklin_insights_quality_highlights_pending_items WHERE entity_type=:entity_type';
        $updatedFamilies = $this->getDbConnection()->executeQuery($sqlQuery, ['entity_type' => PendingItemsRepository::ENTITY_TYPE_PRODUCT])->fetchAll();

        //Only enabled and non variant ones
        $this->assertCount(2, $updatedFamilies);
        $this->assertSame(['1', '2'], array_column($updatedFamilies, 'entity_id'));
    }

    public function test_it_removes_some_updated_products(): void
    {
        $this->getRepository()->addUpdatedProductId(42);
        $this->getRepository()->addUpdatedProductId(123);
        $this->getRepository()->addUpdatedProductId(321);
        $this->getRepository()->addDeletedProductId(456);
        $this->getRepository()->addUpdatedAttributeCode('weight');

        $lock = new Lock('42922021-cec9-4810-ac7a-ace3584f8671');
        $this->getRepository()->acquireLock($lock);

        $this->getRepository()->removeUpdatedProducts([42, 321], $lock);

        $sqlQuery = <<<SQL
        SELECT entity_id FROM pimee_franklin_insights_quality_highlights_pending_items
        WHERE `action` = 'update' AND entity_type = 'product'
SQL;
        $remainingUpdatedProducts = $this->getDbConnection()->query($sqlQuery)->fetchAll();

        $this->assertCount(1, $remainingUpdatedProducts);
        $this->assertEquals('123', $remainingUpdatedProducts[0]['entity_id']);
    }

    private function createTextAttribute(string $attributeCode): void
    {
        $attribute = $this->attributeBuilder->build(
            [
                'code' => $attributeCode,
                'type' => AttributeTypes::TEXT,
                'group' => AttributeGroup::DEFAULT_GROUP_CODE,
            ]
        );
        $this->validator->validate($attribute);
        $this->attributeSaver->save($attribute);
    }

    private function createIdentifierAttribute(string $attributeCode): void
    {
        $attribute = $this->attributeBuilder->build(
            [
                'code' => $attributeCode,
                'type' => AttributeTypes::IDENTIFIER,
                'group' => AttributeGroup::DEFAULT_GROUP_CODE
            ]
        );
        $this->validator->validate($attribute);
        $this->attributeSaver->save($attribute);
    }


    private function createFamily(string $familyCode): void
    {
        $family = $this->familyBuilder->build([
            'code' => $familyCode,
        ]);

        $this->validator->validate($family);
        $this->familySaver->save($family);
    }

    private function createProduct(string $productIdentifier, bool $enabled = true): void
    {
        $product = $this->productBuilder
            ->withIdentifier($productIdentifier)
            ->withStatus($enabled)
            ->build();

        $this->productSaver->save($product);
    }

    private function getRepository(): PendingItemsRepositoryInterface
    {
        return $this->get('akeneo.pim.automation.franklin_insights.repository.quality_highlights_pending_items');
    }

    private function getDbConnection(): Connection
    {
        return $this->get('database_connection');
    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

}
