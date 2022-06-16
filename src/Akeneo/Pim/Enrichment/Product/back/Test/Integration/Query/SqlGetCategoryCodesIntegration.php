<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Enrichment\Product\Integration\Query;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ChangeParent;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetCategories;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetSimpleSelectValue;
use Akeneo\Pim\Enrichment\Product\Domain\Model\ProductIdentifier;
use Akeneo\Pim\Enrichment\Product\Domain\Query\GetCategoryCodes;
use Akeneo\Pim\Enrichment\Product\Infrastructure\Query\SqlGetCategoryCodes;
use Akeneo\Test\Pim\Enrichment\Product\Integration\EnrichmentProductTestCase;
use PHPUnit\Framework\Assert;

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
    public function it_returns_category_codes_for_products()
    {
        $this->createProduct('product_without_category', []);
        $this->createProduct('product_with_categories', [new SetCategories(['suppliers', 'print'])]);

        Assert::assertSame([], $this->sqlGetCategoryCodes->fromProductIdentifiers([]));
        Assert::assertSame(
            ['product_without_category' => []],
            $this->sqlGetCategoryCodes->fromProductIdentifiers([ProductIdentifier::fromString('product_without_category')])
        );
        Assert::assertEqualsCanonicalizing(
            ['product_with_categories' => ['suppliers', 'print']],
            $this->sqlGetCategoryCodes->fromProductIdentifiers([ProductIdentifier::fromString('product_with_categories')])
        );
        Assert::assertEqualsCanonicalizing(
            [
                'product_without_category' => [],
                'product_with_categories' => ['suppliers', 'print'],
            ],
            $this->sqlGetCategoryCodes->fromProductIdentifiers([
                ProductIdentifier::fromString('product_without_category'),
                ProductIdentifier::fromString('product_with_categories'),
                ProductIdentifier::fromString('unknown'),
            ])
        );
    }

    /** @test */
    public function it_returns_category_codes_for_variant_products_and_their_parent()
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

        Assert::assertEqualsCanonicalizing(
            ['variant_product1' => ['print', 'suppliers']],
            $this->sqlGetCategoryCodes->fromProductIdentifiers([ProductIdentifier::fromString('variant_product1')])
        );
        Assert::assertEqualsCanonicalizing(
            ['variant_product2' => ['print', 'suppliers']],
            $this->sqlGetCategoryCodes->fromProductIdentifiers([ProductIdentifier::fromString('variant_product2')])
        );
    }
}
