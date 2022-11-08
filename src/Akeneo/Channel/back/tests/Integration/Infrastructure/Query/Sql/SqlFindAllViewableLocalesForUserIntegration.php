<?php

namespace AkeneoEnterprise\Channel\tests\Integration\Infrastructure\Query\Sql;

use Akeneo\Channel\API\Query\FindAllViewableLocalesForUser;
use Akeneo\Channel\API\Query\Locale;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

final class SqlFindAllViewableLocalesForUserIntegration extends TestCase
{
    /**
     * @test
     */
    public function itReturnsAllViewableLocalesForUser(): void
    {
        $query = $this->getQuery();

        $userId = $this->get('database_connection')
            ->fetchColumn('SELECT id FROM oro_user WHERE username = "mary"', [], 0);

        $expectedLocales = [
            new Locale('en_US', true),
            new Locale('fr_FR', true),
            new Locale('zh_CN', true),
        ];

        Assert::assertEqualsCanonicalizing($expectedLocales, $query->findAll($userId));

        $userId = $this->get('database_connection')
            ->fetchColumn('SELECT id FROM oro_user WHERE username = "julia"', [], 0);

        $expectedLocales = [
            new Locale('en_US', true),
            new Locale('fr_FR', true),
            new Locale('de_DE', true),
            new Locale('zh_CN', true),
        ];

        Assert::assertEqualsCanonicalizing($expectedLocales, $query->findAll($userId));
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    private function getQuery(): FindAllViewableLocalesForUser
    {
        return $this->get('Akeneo\Channel\Infrastructure\Query\Sql\SqlFindAllViewableLocalesForUser');
    }
}
