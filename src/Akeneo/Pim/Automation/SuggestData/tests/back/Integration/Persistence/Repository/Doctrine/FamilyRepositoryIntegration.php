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
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Persistence\Repository\Doctrine\FamilyRepository;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Types\Type;
use PHPUnit\Framework\Assert;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
final class FamilyRepositoryIntegration extends TestCase
{
    private const TEST_FAMILY_CODE = 'test_family';
    private const TEST_FAMILY_LABELS = ['en_US' => 'A family for testing purpose'];

    private const CONTROL_FAMILY_CODE = 'control_family';
    private const CONTROL_FAMILY_LABELS = ['en_US' => 'A control family'];

    private const UNEXPECTED_FAMILY_CODE = 'unexpected_family';

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $testFamily = $this->getFromTestContainer('akeneo_ee_integration_tests.builder.family')->build([
            'code' => self::TEST_FAMILY_CODE,
            'labels' => self::TEST_FAMILY_LABELS,
            'attributes' => ['sku'],
        ]);
        $this->getFromTestContainer('validator')->validate($testFamily);

        $controlFamily = $this->getFromTestContainer('akeneo_ee_integration_tests.builder.family')->build([
            'code' => self::CONTROL_FAMILY_CODE,
            'labels' => self::CONTROL_FAMILY_LABELS,
            'attributes' => ['sku'],
        ]);
        $this->getFromTestContainer('validator')->validate($controlFamily);

        $unexpectedFamily = $this->getFromTestContainer('akeneo_ee_integration_tests.builder.family')->build([
            'code' => self::UNEXPECTED_FAMILY_CODE,
            'attributes' => ['sku'],
        ]);
        $this->getFromTestContainer('validator')->validate($controlFamily);

        $this
            ->getFromTestContainer('pim_catalog.saver.family')
            ->saveAll([$testFamily, $controlFamily, $unexpectedFamily]);

        $controlProduct = $this->createProduct('control_product', self::CONTROL_FAMILY_CODE);
        $this->insertSubscription($controlProduct->getId(), false);
    }

    public function test_that_families_with_subscribed_products_are_found_and_paginated(): void
    {
        $product1 = $this->createProduct('product_1', self::TEST_FAMILY_CODE);
        $this->insertSubscription($product1->getId(), false);
        $product2 = $this->createProduct('product_2', self::TEST_FAMILY_CODE);
        $this->insertSubscription($product2->getId(), true);

        $familyCollection = $this->getRepository()->findBySearch(1, 20, null);

        $this->assertFamilyCollection(
            $familyCollection,
            [
                new Family(self::TEST_FAMILY_CODE, self::TEST_FAMILY_LABELS, Family::MAPPING_PENDING),
                new Family(self::CONTROL_FAMILY_CODE, self::CONTROL_FAMILY_LABELS, Family::MAPPING_FULL),
            ]
        );

        // Assert 1st page of 1 element
        $familyCollection = $this->getRepository()->findBySearch(1, 1, null);
        $this->assertFamilyCollection(
            $familyCollection,
            [new Family(self::TEST_FAMILY_CODE, self::TEST_FAMILY_LABELS, Family::MAPPING_PENDING)]
        );

        // Assert 2nd page of 1 element
        $familyCollection = $this->getRepository()->findBySearch(2, 1, null);
        $this->assertFamilyCollection(
            $familyCollection,
            [new Family(self::CONTROL_FAMILY_CODE, self::CONTROL_FAMILY_LABELS, Family::MAPPING_FULL)]
        );
    }

    public function test_that_only_families_with_subscribed_products_are_fetched(): void
    {
        $product = $this->createProduct('a_product', self::TEST_FAMILY_CODE);
        $this->insertSubscription($product->getId(), false);

        $familyCollection = $this->getRepository()->findBySearch(1, 20, 'control_');
        $this->assertFamilyCollection(
            $familyCollection,
            [new Family(self::CONTROL_FAMILY_CODE, self::CONTROL_FAMILY_LABELS, Family::MAPPING_FULL)]
        );

        $familyCollection = $this->getRepository()->findBySearch(1, 20, 'testing');
        $this->assertFamilyCollection(
            $familyCollection,
            [new Family(self::TEST_FAMILY_CODE, self::TEST_FAMILY_LABELS, Family::MAPPING_FULL)]
        );

        $familyCollection = $this->getRepository()->findBySearch(1, 20, 'family');
        $this->assertFamilyCollection(
            $familyCollection,
            [
                new Family(self::TEST_FAMILY_CODE, self::TEST_FAMILY_LABELS, Family::MAPPING_FULL),
                new Family(self::CONTROL_FAMILY_CODE, self::CONTROL_FAMILY_LABELS, Family::MAPPING_FULL),
            ]
        );
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
     * @return FamilyRepository
     */
    private function getRepository(): FamilyRepository
    {
        return $this->get('akeneo.pim.automation.suggest_data.repository.search_family');
    }

    /**
     * @param FamilyCollection $familyCollection
     * @param Family[] $expectedFamilies
     */
    private function assertFamilyCollection(FamilyCollection $familyCollection, array $expectedFamilies): void
    {
        Assert::assertCount(count($expectedFamilies), $familyCollection);
        foreach ($familyCollection as $position => $family) {
            Assert::assertInstanceOf(Family::class, $family);
            Assert::assertEquals($expectedFamilies[$position]->getCode(), $family->getCode());
            Assert::assertEquals($expectedFamilies[$position]->getLabels(), $family->getLabels());
            Assert::assertEquals($expectedFamilies[$position]->getMappingStatus(), $family->getMappingStatus());
        }
    }
}
