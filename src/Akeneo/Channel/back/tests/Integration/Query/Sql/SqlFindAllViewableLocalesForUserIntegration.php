<?php

namespace Akeneo\Test\Channel\Integration\Query\Sql;

use Akeneo\Channel\API\Query\FindAllViewableLocalesForUser;
use Akeneo\Channel\API\Query\Locale;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

final class SqlFindAllViewableLocalesForUserIntegration extends TestCase
{
    private FindAllViewableLocalesForUser $sqlFindAllViewableLocalesForUser;

    public function setUp(): void
    {
        parent::setUp();

        $this->sqlFindAllViewableLocalesForUser = $this->get(
            'Akeneo\Channel\Infrastructure\Query\Sql\SqlFindAllViewableLocalesForUser'
        );
    }

    /**
     * @group ce
     */
    public function test_it_finds_all_viewable_locales_for_user(): void
    {
        $results = $this->sqlFindAllViewableLocalesForUser->findAll(1);

        $this->assertIsArray($results);
        $this->assertCount(210, $results);
        $this->assertContainsOnlyInstancesOf(Locale::class, $results);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
