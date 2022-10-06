<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Persistence\User;

use Akeneo\Catalogs\Application\Exception\UserNotFoundException;
use Akeneo\Catalogs\Application\Persistence\User\GetUserIdFromUsernameQueryInterface;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;

class GetUserIdFromUsernameQueryTest extends IntegrationTestCase
{
    private ?GetUserIdFromUsernameQueryInterface $getUserIdFromUsernameQuery;

    protected function setUp(): void
    {
        parent::setUp();

        $this->purgeDataAndLoadMinimalCatalog();

        $this->getUserIdFromUsernameQuery = self::getContainer()->get(GetUserIdFromUsernameQueryInterface::class);
    }

    public function testItGetsTheUserIdFromTheUsername(): void
    {
        $this->createUser('julia');

        $userId = $this->getUserIdFromUsernameQuery->execute('julia');
        $this->assertIsInt($userId);
    }

    public function testItFailsIfTheUserDoesNotExist(): void
    {
        $this->expectException(UserNotFoundException::class);
        $this->getUserIdFromUsernameQuery->execute('notjulia');
    }
}
