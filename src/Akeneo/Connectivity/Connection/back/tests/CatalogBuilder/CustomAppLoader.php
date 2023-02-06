<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\CatalogBuilder;

use Akeneo\Connectivity\Connection\Application\CustomApps\Command\CreateCustomAppCommand;
use Akeneo\Connectivity\Connection\Application\CustomApps\Command\CreateCustomAppCommandHandler;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CustomAppLoader
{
    public function __construct(private readonly CreateCustomAppCommandHandler $createCustomAppCommandHandler)
    {
    }

    public function create(
        string $clientId,
        int $userId,
        string $name = 'custom_app_name',
        string $activateUrl = 'http://activate.test',
        string $callbackUrl = 'http://callback.test',
    ): void {
        $createCommand = new CreateCustomAppCommand(
            $clientId,
            $name,
            $activateUrl,
            $callbackUrl,
            $userId,
        );

        $this->createCustomAppCommandHandler->handle($createCommand);
    }
}
