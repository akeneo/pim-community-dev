<?php

namespace Akeneo\Test\Channel\Integration\Query\Sql;

use Akeneo\Channel\API\Query\FindAllEditableLocalesForUser;
use Akeneo\Channel\API\Query\Locale;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

final class SqlFindAllEditableLocalesForUserIntegration extends TestCase
{
    private FindAllEditableLocalesForUser $sqlFindAllEditableLocalesForUser;

    public function setUp(): void
    {
        parent::setUp();

        $this->sqlFindAllEditableLocalesForUser = $this->get(
            'Akeneo\Channel\Infrastructure\Query\Sql\SqlFindAllEditableLocalesForUser'
        );
    }

    /**
     * @group ce
     */
    public function test_it_finds_all_editable_locales_for_user(): void
    {
        $results = $this->sqlFindAllEditableLocalesForUser->findAll(1);

        $this->assertIsArray($results);
        $this->assertCount(210, $results);
        $this->assertContainsOnlyInstancesOf(Locale::class, $results);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
