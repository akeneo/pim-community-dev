<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Marketplace\TestApps\Command;

use Akeneo\Connectivity\Connection\Domain\Marketplace\TestApps\Persistence\DeleteTestAppQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Marketplace\TestApps\Persistence\GetTestAppQueryInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DeleteTestAppHandler
{
    public function __construct(
        private GetTestAppQueryInterface $getTestAppQuery,
        private DeleteTestAppQueryInterface $deleteTestAppQuery,
    ) {
    }

    public function handle(DeleteTestAppCommand $testAppCommand): void
    {
        $testAppId = $testAppCommand->getTestAppId();

        $testAppData = $this->getTestAppQuery->execute($testAppId);
        if (null === $testAppData) {
            throw new \InvalidArgumentException(\sprintf('Test app with %s client_id was not found.', $testAppId));
        }

        $this->deleteTestAppQuery->execute($testAppId);
    }
}
