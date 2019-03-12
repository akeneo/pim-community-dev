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

namespace Akeneo\Pim\Automation\FranklinInsights\tests\back\Integration\Elasticsearch;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Types\Type;
use PHPUnit\Framework\Assert;

/**
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class FranklinSubscriptionFilterIntegration extends TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $family = $this->createFamily('family');
        $product1 = $this->createProduct('product1', $family->getCode());
        $product2 = $this->createProduct('product2', $family->getCode());
        $this->createProduct('product3', $family->getCode());

        $this->insertSubscription($product1->getId(), true);
        $this->insertSubscription($product2->getId(), true);

        $this->get('pim_catalog.elasticsearch.indexer.product')->indexAll([$product1, $product2]);
    }

    public function test_products_are_filterable_by_franklin_subscription(): void
    {
        $pqbFactoryService = 'akeneo.pim.enrichment.query.product_and_product_model_query_builder_from_size_factory.' .
            'with_product_identifier_cursor';
        $pqbFactory = $this->get($pqbFactoryService);
        $factoryParameters = [
            'repository_parameters' => [],
            'repository_method' => 'createQueryBuilder',
            'default_locale' => 'en_US',
            'default_scope' => 'ecommerce',
            'limit' => 25,
            'from' => 0,
        ];
        $subPqb = $pqbFactory->create($factoryParameters);
        $notSubPqb = $pqbFactory->create($factoryParameters);

        $subscribedProducts = $subPqb->addFilter('franklin_subscription', '=', true)->execute();
        $notSubscribedProducts = $notSubPqb->addFilter('franklin_subscription', '=', false)->execute();

        Assert::assertCount(2, $subscribedProducts);
        Assert::assertCount(1, $notSubscribedProducts);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function createProduct(string $identifier, string $familyCode): ProductInterface
    {
        $product = $this->getFromTestContainer('akeneo_integration_tests.catalog.product.builder')
            ->withIdentifier($identifier)
            ->withFamily($familyCode)
            ->withCategories('master')
            ->build();
        $violations = $this->getFromTestContainer('validator')->validate($product);
        Assert::assertSame(0, $violations->count(), sprintf('Product "%s" is not valid.', $identifier));

        $this->getFromTestContainer('pim_catalog.saver.product')->save($product);

        return $product;
    }

    /**
     * @param int $productId
     * @param bool $isMappingMissing
     */
    private function insertSubscription(int $productId, bool $isMappingMissing): void
    {
        $query = <<<SQL
INSERT INTO pimee_franklin_insights_subscription (product_id, subscription_id, misses_mapping) 
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

    private function createFamily(string $familyCode): FamilyInterface
    {
        $family = $this
            ->getFromTestContainer('akeneo_ee_integration_tests.builder.family')
            ->build(['code' => $familyCode, 'attributes' => ['sku']]);
        $violations = $this->getFromTestContainer('validator')->validate($family);
        Assert::assertSame(0, $violations->count(), 'Family is not valid.');
        $this->getFromTestContainer('pim_catalog.saver.family')->save($family);

        return $family;
    }
}
