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

        Assert::assertSame(['en_US', 'fr_FR', 'zh_CN'], $query->fetchAll($userId));
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    private function getQuery(): FindAllViewableLocalesForUserInterface
    {
        return $this->get('Akeneo\Channel\Infrastructure\Query\Sql\SqlFindAllViewableLocalesForUser');
    }

}
