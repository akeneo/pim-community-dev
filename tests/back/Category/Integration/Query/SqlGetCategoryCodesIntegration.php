<?php

declare(strict_types=1);

namespace Akeneo\Test\Category\Integration\Query;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ChangeParent;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetCategories;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetSimpleSelectValue;
use Akeneo\Pim\Enrichment\Product\Domain\Query\GetCategoryCodes;
use Akeneo\Pim\Enrichment\Product\Infrastructure\Query\SqlGetCategoryCodes;
use Akeneo\Test\Pim\Enrichment\Product\Integration\EnrichmentProductTestCase;
use PHPUnit\Framework\Assert;
use Ramsey\Uuid\Uuid;

final class SqlGetCategoryCodesIntegration extends EnrichmentProductTestCase
{
    private SqlGetCategoryCodes $sqlGetCategoryCodes;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sqlGetCategoryCodes = $this->get(GetCategoryCodes::class);

        $this->loadEnrichmentProductFunctionalFixtures();
    }

    /** @test */
    public function it_returns_category_codes_for_product_uuids()
    {
        $this->createProduct('product_without_category', []);
        $this->createProduct('product_with_categories', [new SetCategories(['suppliers', 'print'])]);
        $productWithoutCategoriesUuid = $this->get('pim_catalog.repository.product')->findOneByIdentifier('product_without_category')->getUuid();
        $productWithCategoriesUuid = $this->get('pim_catalog.repository.product')->findOneByIdentifier('product_with_categories')->getUuid();

        Assert::assertSame([], $this->sqlGetCategoryCodes->fromProductUuids([]));
        Assert::assertSame(
            [$productWithoutCategoriesUuid->toString() => []],
            $this->sqlGetCategoryCodes->fromProductUuids([$productWithoutCategoriesUuid])
        );
        Assert::assertEqualsCanonicalizing(
            [$productWithCategoriesUuid->toString() => ['suppliers', 'print']],
            $this->sqlGetCategoryCodes->fromProductUuids([$productWithCategoriesUuid])
        );
        Assert::assertEqualsCanonicalizing(
            [
                $productWithoutCategoriesUuid->toString() => [],
                $productWithCategoriesUuid->toString() => ['suppliers', 'print'],
            ],
            $this->sqlGetCategoryCodes->fromProductUuids([
                $productWithoutCategoriesUuid,
                $productWithCategoriesUuid,
                Uuid::uuid4(),
            ])
        );
    }

    /** @test */
    public function it_returns_category_codes_for_variant_product_uuids()
    {
        $this->createProductModel('root', 'color_variant_accessories', [
            'categories' => ['print'],
        ]);
        $this->createProduct('variant_product1', [
            new ChangeParent('root'),
            new SetCategories(['suppliers']),
            new SetSimpleSelectValue('main_color', null, null, 'red')
        ]);
        $this->createProduct('variant_product2', [
            new ChangeParent('root'),
            new SetCategories(['suppliers', 'print']),
            new SetSimpleSelectValue('main_color', null, null, 'green')
        ]);
        $variantProduct1 = $this->get('pim_catalog.repository.product')->findOneByIdentifier('variant_product1')->getUuid();
        $variantProduct2 = $this->get('pim_catalog.repository.product')->findOneByIdentifier('variant_product2')->getUuid();

        Assert::assertEqualsCanonicalizing(
            ['variant_product1' => ['print', 'suppliers']],
            $this->sqlGetCategoryCodes->fromProductUuids([$variantProduct1])
        );
        Assert::assertEqualsCanonicalizing(
            ['variant_product2' => ['print', 'suppliers']],
            $this->sqlGetCategoryCodes->fromProductUuids([$variantProduct2])
        );
    }
}
