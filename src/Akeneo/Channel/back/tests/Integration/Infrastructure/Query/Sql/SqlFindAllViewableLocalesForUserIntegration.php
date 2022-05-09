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
    public function it_returns_all_viewable_locales()
    {
        $query = $this->getQuery();

        $userId =  $this->get('database_connection')
            ->fetchColumn('SELECT id FROM oro_user WHERE username = "mary"', [], 0);

        $expectedLocales = [
            new Locale('en_US', 1),
            new Locale('fr_FR', 1),
            new Locale('zh_CN', 1),
        ];

        Assert::assertEqualsCanonicalizing($expectedLocales, $query->fetchAll($userId));
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
