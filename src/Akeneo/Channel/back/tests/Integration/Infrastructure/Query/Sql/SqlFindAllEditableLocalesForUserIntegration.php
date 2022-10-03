<?php

namespace AkeneoEnterprise\Channel\tests\Integration\Infrastructure\Query\Sql;

use Akeneo\Channel\API\Query\FindAllEditableLocalesForUser;
use Akeneo\Channel\API\Query\Locale;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

final class SqlFindAllEditableLocalesForUserIntegration extends TestCase
{
    /**
     * @test
     */
    public function itReturnsAllEditableLocalesForUser(): void
    {
        $query = $this->getQuery();

        $userId = $this->get('database_connection')
            ->fetchOne('SELECT id FROM oro_user WHERE username = "mary"');

        $expectedLocales = [
            new Locale('en_US', true),
        ];

        Assert::assertEqualsCanonicalizing($expectedLocales, $query->findAll($userId));

        $userId = $this->get('database_connection')
            ->fetchOne('SELECT id FROM oro_user WHERE username = "julia"');

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

    private function getQuery(): FindAllEditableLocalesForUser
    {
        return $this->get('Akeneo\Channel\Infrastructure\Query\Sql\SqlFindAllEditableLocalesForUser');
    }
}
