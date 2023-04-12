<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Persistence\User;

use Akeneo\Catalogs\Application\Exception\UserNotFoundException;
use Akeneo\Catalogs\Application\Persistence\User\GetUserIdFromUsernameQueryInterface;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;

class GetUserIdFromUsernameQueryTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->purgeDataAndLoadMinimalCatalog();
    }

    public function testItGetsTheUserIdFromTheUsername(): void
    {
        $this->createUser('julia');

        $userId = self::getContainer()->get(GetUserIdFromUsernameQueryInterface::class)->execute('julia');
        $this->assertIsInt($userId);
    }

    public function testItFailsIfTheUserDoesNotExist(): void
    {
        $this->expectException(UserNotFoundException::class);
        self::getContainer()->get(GetUserIdFromUsernameQueryInterface::class)->execute('notjulia');
    }
}
