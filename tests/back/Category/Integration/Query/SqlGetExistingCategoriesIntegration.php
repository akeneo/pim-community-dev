<?php

declare(strict_types=1);

namespace Akeneo\Test\Category\Integration\Query;

use Akeneo\Pim\Enrichment\Category\API\Query\GetViewableCategories;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

/**
 * @group ce
 */
final class SqlGetExistingCategoriesIntegration extends TestCase
{
    private GetViewableCategories $getViewableCategories;

    protected function setUp(): void
    {
        parent::setUp();
        $this->getViewableCategories = $this->get(GetViewableCategories::class);
    }

    /** @test */
    public function it_returns_existing_category_codes()
    {
        $this->createCategory(['code' => 'uno']);
        $this->createCategory(['code' => 'dos']);
        $this->createCategory(['code' => 'tres']);

        Assert::assertSame([], $this->getViewableCategories->forUserId([], 1));
        Assert::assertEqualsCanonicalizing(
            ['uno', 'dos'],
            $this->getViewableCategories->forUserId(['uno', 'dos', 'cinco'], 99)
        );
    }

    /** @test */
    public function it_throws_exceptions_on_integer_value()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected a string. Got: integer');

        $this->getViewableCategories->forUserId(['toto', 42], 1);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
