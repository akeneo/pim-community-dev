<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Test\Pim\Automation\SuggestData\Integration\Persistence\Repository\Doctrine;

use Akeneo\Pim\Automation\SuggestData\Domain\AttributeMapping\Model\Read\Family;
use Akeneo\Pim\Automation\SuggestData\Domain\AttributeMapping\Model\Read\FamilyCollection;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Types\Type;
use PHPUnit\Framework\Assert;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class FamilyRepositoryIntegration extends TestCase
{
    private const TEST_FAMILY_CODE = 'test_family';
    private const CONTROL_FAMILY_CODE = 'control_family';

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $testFamily = $this->getFromTestContainer('akeneo_ee_integration_tests.builder.family')->build([
            'code' => static::TEST_FAMILY_CODE,
            'labels' => [
                'en_US' => 'A family for testing purpose',
            ],
            'attributes' => ['sku'],
        ]);
        $this->getFromTestContainer('validator')->validate($testFamily);

        $controlFamily = $this->getFromTestContainer('akeneo_ee_integration_tests.builder.family')->build([
            'code' => static::CONTROL_FAMILY_CODE,
            'labels' => [
                'en_US' => 'A control family',
            ],
            'attributes' => ['sku'],
        ]);
        $this->getFromTestContainer('validator')->validate($controlFamily);

        $this->getFromTestContainer('pim_catalog.saver.family')->saveAll([$testFamily, $controlFamily]);

        $controlProduct = $this->createProduct('control_product', static::CONTROL_FAMILY_CODE);
        $this->insertSubscription($controlProduct->getId(), false);
    }

    public function test_that_families_with_subscribed_products_are_found(): void
    {
        $product = $this->createProduct('a_product', static::TEST_FAMILY_CODE);
        $this->insertSubscription($product->getId(), false);

        $familyCollection = $this->getFamilies(
            1,
            20,
            null,
            [static::TEST_FAMILY_CODE, static::CONTROL_FAMILY_CODE]
        );

        $this->assertFamilyCollectionContainsInOrder(
            $familyCollection,
            [static::TEST_FAMILY_CODE, static::CONTROL_FAMILY_CODE]
        );
    }

    public function test_that_families_with_subscribed_products_are_paginated(): void
    {
        $product = $this->createProduct('a_product', static::TEST_FAMILY_CODE);
        $this->insertSubscription($product->getId(), false);

        $familyCollection = $this->getFamilies(
            1,
            1,
            null,
            [static::TEST_FAMILY_CODE, static::CONTROL_FAMILY_CODE]
        );

        $this->assertFamilyCollectionContainsInOrder(
            $familyCollection,
            [static::TEST_FAMILY_CODE]
        );
    }

    public function test_that_families_with_subscribed_products_are_searched_using_code(): void
    {
        $product = $this->createProduct('a_product', static::TEST_FAMILY_CODE);
        $this->insertSubscription($product->getId(), false);

        $familyCollection = $this->getFamilies(
            1,
            20,
            'control_',
            [static::TEST_FAMILY_CODE, static::CONTROL_FAMILY_CODE]
        );

        $this->assertFamilyCollectionContainsInOrder(
            $familyCollection,
            [static::CONTROL_FAMILY_CODE]
        );
    }

    public function test_that_families_with_subscribed_products_are_searched_using_label(): void
    {
        $product = $this->createProduct('a_product', static::TEST_FAMILY_CODE);
        $this->insertSubscription($product->getId(), false);

        $familyCollection = $this->getFamilies(
            1,
            20,
            'testing',
            [static::TEST_FAMILY_CODE, static::CONTROL_FAMILY_CODE]
        );

        $this->assertFamilyCollectionContainsInOrder(
            $familyCollection,
            [static::TEST_FAMILY_CODE]
        );
    }

    public function test_that_only_families_with_subscribed_products_are_found(): void
    {
        $this->createProduct('a_product', static::TEST_FAMILY_CODE);

        $familyCollection = $this->getFamilies(
            1,
            20,
            null,
            [static::TEST_FAMILY_CODE, static::CONTROL_FAMILY_CODE]
        );

        $this->assertFamilyCollectionContainsInOrder(
            $familyCollection,
            [static::CONTROL_FAMILY_CODE]
        );
    }

    public function test_that_no_families_are_returned_if_their_products_are_not_subscribed(): void
    {
        $this->createProduct('product', static::TEST_FAMILY_CODE);

        $familyCollection = $this->getFamilies(1, 20, null, [static::TEST_FAMILY_CODE]);

        Assert::assertEmpty($familyCollection);
    }

    public function test_that_no_families_are_returned_if_they_have_no_products(): void
    {
        $familyCollection = $this->getFamilies(1, 20, null, [static::TEST_FAMILY_CODE]);

        Assert::assertEmpty($familyCollection);
    }

    public function test_that_the_status_is_unmapped_if_there_is_at_least_one_subscription_with_missing_mapping(): void
    {
        $product1 = $this->createProduct('product_1', static::TEST_FAMILY_CODE);
        $this->insertSubscription($product1->getId(), false);
        $product2 = $this->createProduct('product_2', static::TEST_FAMILY_CODE);
        $this->insertSubscription($product2->getId(), true);

        $familyCollection = $this->getFamilies(1, 20, null, [static::TEST_FAMILY_CODE]);

        $this->assertFamilyStatus($familyCollection, Family::MAPPING_PENDING);
    }

    public function test_that_the_status_is_mapped_if_no_subscription_indicates_missing_mapping(): void
    {
        $product1 = $this->createProduct('product_1', static::TEST_FAMILY_CODE);
        $this->insertSubscription($product1->getId(), false);
        $product2 = $this->createProduct('product_2', static::TEST_FAMILY_CODE);
        $this->insertSubscription($product2->getId(), false);

        $familyCollection = $this->getFamilies(1, 20, null, [static::TEST_FAMILY_CODE]);

        $this->assertFamilyStatus($familyCollection, Family::MAPPING_FULL);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    /**
     * @param string $identifier
     * @param string $familyCode
     *
     * @return ProductInterface
     */
    private function createProduct(string $identifier, string $familyCode): ProductInterface
    {
        $product = $this->get('pim_catalog.builder.product')->createProduct($identifier, $familyCode);
        $this->get('validator')->validate($product);
        $this->get('pim_catalog.saver.product')->save($product);

        return $product;
    }

    /**
     * @param int $productId
     * @param bool $isMappingMissing
     */
    private function insertSubscription(int $productId, bool $isMappingMissing): void
    {
        $query = <<<SQL
INSERT INTO pim_suggest_data_product_subscription (product_id, subscription_id, misses_mapping) 
VALUES (:productId, :subscriptionId, :isMappingMissing)
SQL;

        $queryParameters = [
            'productId' => $productId,
            'subscriptionId' => uniqid(),
            'isMappingMissing' => $isMappingMissing,
        ];
        $types = [
            'productId' => Type::INTEGER,
            'subscriptionId' => Type::STRING,
            'isMappingMissing' => Type::BOOLEAN,
        ];

        $this->get('doctrine.orm.entity_manager')->getConnection()->executeUpdate($query, $queryParameters, $types);
    }

    /**
     * @param int $page
     * @param int $limit
     * @param string|null $search
     * @param string[] $identifiers
     *
     * @return FamilyCollection
     */
    private function getFamilies(int $page, int $limit, ?string $search, array $identifiers): FamilyCollection
    {
        return $this
            ->get('akeneo.pim.automation.suggest_data.repository.search_family')
            ->findBySearch($page, $limit, $search, $identifiers);
    }

    /**
     * @param FamilyCollection $familyCollection
     * @param string[] $identifiers
     */
    private function assertFamilyCollectionContainsInOrder(FamilyCollection $familyCollection, array $identifiers): void
    {
        Assert::assertCount(count($identifiers), $familyCollection);
        foreach ($familyCollection as $position => $family) {
            Assert::assertSame($identifiers[$position], $family->getCode());
        }
    }

    private function assertFamilyStatus(FamilyCollection $familyCollection, int $mappingStatus): void
    {
        Assert::assertCount(1, $familyCollection);
        foreach ($familyCollection as $position => $family) {
            Assert::assertSame($mappingStatus, $family->getMappingStatus());
        }
    }
}
