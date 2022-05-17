<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Persistence;

use Akeneo\Catalogs\Infrastructure\Persistence\FindOneCatalogByIdQuery;
use Akeneo\Catalogs\ServiceAPI\Command\CreateCatalogCommand;
use Akeneo\Catalogs\ServiceAPI\Messenger\CommandBus;
use Akeneo\Catalogs\ServiceAPI\Model\Catalog;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FindOneCatalogByIdQueryTest extends IntegrationTestCase
{
    private ?FindOneCatalogByIdQuery $query;
    private ?CommandBus $commandBus;

    public function setUp(): void
    {
        parent::setUp();

        $this->purgeDataAndLoadMinimalCatalog();

        $this->query = self::getContainer()->get(FindOneCatalogByIdQuery::class);
        $this->commandBus = self::getContainer()->get(CommandBus::class);
    }

    public function testItFindsTheCatalog(): void
    {
        $owner = $this->createUser('test');
        $id = 'db1079b6-f397-4a6a-bae4-8658e64ad47c';
        $this->commandBus->execute(new CreateCatalogCommand(
            $id,
            'Store US',
            $owner->getId(),
        ));

        $result = $this->query->execute($id);
        $expected = new Catalog($id, 'Store US', $owner->getId());

        $this->assertEquals($expected, $result);
    }

    public function testItReturnsNullIfUnknownId(): void
    {
        $result = $this->query->execute('db1079b6-f397-4a6a-bae4-8658e64ad47c');

        $this->assertNull($result);
    }
}
