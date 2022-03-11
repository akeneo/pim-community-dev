<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Enrichment\Category\Integration\Query;

use Akeneo\Pim\Enrichment\Category\API\Query\GetExistingCategories;
use Akeneo\Pim\Enrichment\Category\API\Query\SqlGetExistingCategories;
use Akeneo\Test\Pim\Enrichment\Product\Integration\EnrichmentProductTestCase;
use PHPUnit\Framework\Assert;

final class SqlGetExistingCategoriesIntegration extends EnrichmentProductTestCase
{
    private SqlGetExistingCategories $getExistingCategories;

    protected function setUp(): void
    {
        parent::setUp();
        $this->getExistingCategories = $this->get(GetExistingCategories::class);
    }

    /** @test */
    public function it_returns_category_codes_for_products()
    {
        $this->createCategory(['code' => 'uno']);
        $this->createCategory(['code' => 'dos']);
        $this->createCategory(['code' => 'tres']);

        Assert::assertSame([], $this->getExistingCategories->forCodes([]));
        Assert::assertEqualsCanonicalizing(['uno', 'dos'], $this->getExistingCategories->forCodes(['uno', 'dos', 'cinco']));
    }

    /** @test */
    public function it_throws_exceptions_on_integer_value()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected a string. Got: integer');

        $this->getExistingCategories->forCodes(['toto', 42]);
    }

    /** @test */
    public function it_throws_exceptions_on_empty_value()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected a different value than "".');

        $this->getExistingCategories->forCodes(['toto', '']);
    }
}
