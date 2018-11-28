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

namespace Akeneo\Pim\Automation\SuggestData\tests\back\Integration\Persistence\Query\AttributeMapping;

use Akeneo\Pim\Automation\SuggestData\Domain\AttributeMapping\Model\Read\Family;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Types\Type;
use PHPUnit\Framework\Assert;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class GetAttributeMappingStatusesFromFamilyCodesQueryIntegration extends TestCase
{
    private const TESTED_FAMILY_CODE = 'test_family';
    private const CONTROL_FAMILY_CODE = 'control_family';

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $family1 = $this->getFromTestContainer('akeneo_ee_integration_tests.builder.family')->build([
            'code' => static::TESTED_FAMILY_CODE,
            'labels' => [
                'en_US' => 'A test family',
            ],
            'attributes' => ['sku'],
        ]);
        $this->getFromTestContainer('validator')->validate($family1);

        $family2 = $this->getFromTestContainer('akeneo_ee_integration_tests.builder.family')->build([
            'code' => static::CONTROL_FAMILY_CODE,
            'labels' => [
                'en_US' => 'A test family',
            ],
            'attributes' => ['sku'],
        ]);
        $this->getFromTestContainer('validator')->validate($family2);

        $this->getFromTestContainer('pim_catalog.saver.family')->saveAll([$family1, $family2]);

        $controlProduct = $this->createProduct('control_product', static::CONTROL_FAMILY_CODE);
        $this->insertSubscription($controlProduct->getId(), false);
    }

    public function test_the_status_is_unmapped_if_there_is_at_least_one_subscription_with_missing_mapping(): void
    {
        $product1 = $this->createProduct('product_1', static::TESTED_FAMILY_CODE);
        $this->insertSubscription($product1->getId(), false);
        $product2 = $this->createProduct('product_2', static::TESTED_FAMILY_CODE);
        $this->insertSubscription($product2->getId(), true);

        $attributeMappingStatus = $this->getAttributeMappingStatusForTestFamily();

        Assert::assertSame(
            [static::TESTED_FAMILY_CODE => Family::MAPPING_PENDING],
            $attributeMappingStatus
        );
    }

    public function test_the_status_is_mapped_if_no_subscription_indicates_missing_mapping(): void
    {
        $product1 = $this->createProduct('product_1', static::TESTED_FAMILY_CODE);
        $this->insertSubscription($product1->getId(), false);
        $product2 = $this->createProduct('product_2', static::TESTED_FAMILY_CODE);
        $this->insertSubscription($product2->getId(), false);

        $attributeMappingStatus = $this->getAttributeMappingStatusForTestFamily();

        Assert::assertSame(
            [static::TESTED_FAMILY_CODE => Family::MAPPING_FULL],
            $attributeMappingStatus
        );
    }

    public function test_there_is_no_mapping_status_for_families_with_no_subscription(): void
    {
        $this->createProduct('product_1', static::TESTED_FAMILY_CODE);
        $this->createProduct('product_2', static::TESTED_FAMILY_CODE);

        $attributeMappingStatus = $this->getAttributeMappingStatusForTestFamily();

        Assert::assertEmpty($attributeMappingStatus);
    }

    public function test_there_is_no_mapping_status_for_families_with_no_products(): void
    {
        $attributeMappingStatus = $this->getAttributeMappingStatusForTestFamily();

        Assert::assertEmpty($attributeMappingStatus);
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
     * @return array
     */
    private function getAttributeMappingStatusForTestFamily(): array
    {
        return $this
            ->get('akeneo.pim.automation.suggest_data.infrastructure.persistence.query.attribute_mapping.get_statuses_for_families')
            ->execute([static::TESTED_FAMILY_CODE]);
    }
}
