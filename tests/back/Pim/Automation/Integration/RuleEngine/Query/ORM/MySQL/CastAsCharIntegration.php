<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AkeneoTestEnterprise\Pim\Automation\Integration\RuleEngine\Query\ORM\MySQL;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleDefinition;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\Assert;

/**
 * @todo @merge master/6.0: remove this test class
 */
final class CastAsCharIntegration extends TestCase
{
    private QueryBuilder $queryBuilder;

    /** @test */
    public function it_casts_a_constant_value_as_char(): void
    {
        $this->queryBuilder->select("CASTASCHAR(123.456)");
        $this->assertSql('/^SELECT CAST\(123.456 AS CHAR\)/');
    }

    /** @test */
    public function it_casts_an_expression_as_char(): void
    {
        $this->queryBuilder->select('CASTASCHAR(rule.id)');
        $this->assertSql('/^SELECT CAST\(\w+\.id AS CHAR\)/');
    }

    /** @test */
    public function it_casts_a_constant_value_as_char_using_a_defined_collation(): void
    {
        $this->queryBuilder->select('CASTASCHAR(1000, utf8mb4_unicode_ci)');
        $this->assertSql('/^SELECT CAST\(1000 AS CHAR\) COLLATE utf8mb4_unicode_ci/');
    }

    /** @test */
    public function it_casts_a_property_as_char_using_a_defined_collation(): void
    {
        $this->queryBuilder->select('CASTASCHAR(rule.id, utf8mb4_unicode_ci)');
        $this->assertSql('/^SELECT CAST\(\w+\.id AS CHAR\) COLLATE utf8mb4_unicode_ci/');
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->queryBuilder = $this->get('doctrine.orm.entity_manager')->createQueryBuilder()->from(
            RuleDefinition::class,
            'rule'
        );
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function assertSql(string $pattern): void
    {
        Assert::assertRegExp(
            $pattern,
            $this->queryBuilder->getQuery()->getSQL()
        );
    }
}
