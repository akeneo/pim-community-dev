<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Test\Pim\Enrichment\Product\Integration\Query;

use Akeneo\Pim\Enrichment\Product\back\Test\Integration\EnrichmentProductTestCase;
use Akeneo\Pim\Enrichment\Product\Domain\Model\ProductIdentifier;
use Akeneo\Pim\Enrichment\Product\Domain\Query\GetCategoryCodes;
use Akeneo\Pim\Enrichment\Product\Infrastructure\Query\SqlGetCategoryCodes;
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
        $this->createProduct('product_with_categories', ['categories' => ['suppliers', 'print']]);

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
            ])
        );
    }

    /** @test */
    public function it_returns_category_codes_for_variant_products()
    {
        $this->createProductModel('root', 'color_variant_accessories', [
            'categories' => ['print'],
        ]);
        $this->createProduct('variant_product1', [
            'parent' => 'root',
            'categories' => ['suppliers'],
            'values' => ['main_color' => [['locale' => null, 'scope' => null, 'data' => 'red']]],
        ]);
        $this->createProduct('variant_product2', [
            'parent' => 'root',
            'categories' => ['suppliers', 'print'],
            'values' => ['main_color' => [['locale' => null, 'scope' => null, 'data' => 'green']]],
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
