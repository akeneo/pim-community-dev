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

namespace Akeneo\Pim\Automation\FranklinInsights\tests\back\Integration\Persistence\Query\Doctrine\QualityHighlights;

use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\Query\SelectUpdatedProductsIdsToApplyQueryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\ValueObject\BatchSize;
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\ValueObject\Lock;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Persistence\Query\Doctrine\QualityHighlights\SelectUpdatedProductsIdsToApplyQuery;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Persistence\Repository\Doctrine\QualityHighlights\PendingItemsRepository;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

class SelectUpdatedProductsIdsToApplyQueryIntegration extends TestCase
{
    public function test_it_selects_only_updated_product_ids_that_must_be_applied()
    {
        $this->createFamily('a_family');
        $this->createFamily('updated_family');
        $this->insertUpdatedFamily('updated_family');

        $product1 = $this->createProduct('updated_product_1', 'a_family');
        $product2 = $this->createProduct('updated_product_2', 'a_family');
        $product3 = $this->createProduct('updated_product_3', 'a_family');
        $product4 = $this->createProduct('updated_product_4', 'updated_family');

        $lock = '42922021-cec9-4810-ac7a-ace3584f8671';
        $this->insertUpdatedProduct($product1->getId(), $lock);
        $this->insertUpdatedProduct($product2->getId(), $lock);
        $this->insertUpdatedProduct($product3->getId(), '');
        $this->insertUpdatedProduct($product4->getId(), $lock);

        $results = $this->getQuery()->execute(new Lock($lock), new BatchSize(100));
        $expectedProductIds = [$product1->getId(), $product2->getId()];

        $this->assertEqualsCanonicalizing($expectedProductIds, $results);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function insertUpdatedFamily(string $familyCode)
    {
        $this->getFromTestContainer('database_connection')->executeQuery($this->getInsertSql(), [
            'entity_type' => PendingItemsRepository::ENTITY_TYPE_FAMILY,
            'entity_id' => $familyCode,
            'action' => PendingItemsRepository::ACTION_ENTITY_UPDATED,
            'lock' => '',
        ]);
    }

    private function insertUpdatedProduct(int $productId, string $lock)
    {
        $this->getFromTestContainer('database_connection')->executeQuery($this->getInsertSql(), [
            'entity_type' => PendingItemsRepository::ENTITY_TYPE_PRODUCT,
            'entity_id' => $productId,
            'action' => PendingItemsRepository::ACTION_ENTITY_UPDATED,
            'lock' => $lock,
        ]);
    }

    private function getInsertSql(): string
    {
        return <<<SQL
            INSERT INTO pimee_franklin_insights_quality_highlights_pending_items (entity_type, entity_id, `action`, lock_id)
            VALUES (:entity_type, :entity_id, :action, :lock);
SQL;
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

    private function getQuery(): SelectUpdatedProductsIdsToApplyQueryInterface
    {
        return $this->getFromTestContainer(SelectUpdatedProductsIdsToApplyQuery::class);
    }
}
