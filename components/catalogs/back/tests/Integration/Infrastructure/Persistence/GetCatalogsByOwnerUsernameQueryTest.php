<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Persistence;

use Akeneo\Catalogs\Infrastructure\Persistence\GetCatalogsByOwnerUsernameQuery;
use Akeneo\Catalogs\ServiceAPI\Command\CreateCatalogCommand;
use Akeneo\Catalogs\ServiceAPI\Messenger\CommandBus;
use Akeneo\Catalogs\ServiceAPI\Model\Catalog;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetCatalogsByOwnerUsernameQueryTest extends IntegrationTestCase
{
    private ?GetCatalogsByOwnerUsernameQuery $query;
    private ?CommandBus $commandBus;

    public function setUp(): void
    {
        parent::setUp();

        $this->purgeDataAndLoadMinimalCatalog();

        $this->query = self::getContainer()->get(GetCatalogsByOwnerUsernameQuery::class);
        $this->commandBus = self::getContainer()->get(CommandBus::class);
    }

    public function testItGetsPaginatedCatalogsByOwnerUsername(): void
    {
        $owner = $this->createUser('owner');
        $ownerId = $owner->getId();
        $anotherUserId = $this->createUser('another_user')->getId();
        $idUS = 'db1079b6-f397-4a6a-bae4-8658e64ad47c';
        $idFR = 'ed30425c-d9cf-468b-8bc7-fa346f41dd07';
        $idUK = '27c53e59-ee6a-4215-a8f1-2fccbb67ba0d';
        $idJP = '34478398-d77b-44d6-8a71-4d9ba4cb2c3b';
        $this->commandBus->execute(new CreateCatalogCommand(
            $idUS,
            'Store US',
            $ownerId,
        ));
        $this->commandBus->execute(new CreateCatalogCommand(
            $idFR,
            'Store FR',
            $ownerId,
        ));
        $this->commandBus->execute(new CreateCatalogCommand(
            $idJP,
            'Store JP',
            $anotherUserId,
        ));
        $this->commandBus->execute(new CreateCatalogCommand(
            $idUK,
            'Store UK',
            $ownerId,
        ));

        $resultFirstPage = $this->query->execute($owner->getUserIdentifier(), 0, 2);
        $expectedFirstPage = [
            new Catalog($idUK, 'Store UK', $ownerId),
            new Catalog($idUS, 'Store US', $ownerId),
        ];
        $this->assertEquals($expectedFirstPage, $resultFirstPage);

        $resultSecondPage = $this->query->execute($owner->getUserIdentifier(), 2, 2);
        $expectedSecondPage = [
            new Catalog($idFR, 'Store FR', $ownerId),
        ];
        $this->assertEquals($expectedSecondPage, $resultSecondPage);
    }
}
