<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\CatalogBuilder;

use Akeneo\Connectivity\Connection\Application\Marketplace\TestApps\Command\CreateTestAppCommand;
use Akeneo\Connectivity\Connection\Application\Marketplace\TestApps\Command\CreateTestAppCommandHandler;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TestAppLoader
{
    public function __construct(private CreateTestAppCommandHandler $createTestAppCommandHandler)
    {
    }

    public function create(
        string $clientId,
        int $userId,
        string $name = 'test_app_name',
        string $activateUrl = 'http://activate.test',
        string $callbackUrl = 'http://callback.test',
    ): void {
        $createCommand = new CreateTestAppCommand(
            $clientId,
            $name,
            $activateUrl,
            $callbackUrl,
            $userId,
        );

        $this->createTestAppCommandHandler->handle($createCommand);
    }
}
