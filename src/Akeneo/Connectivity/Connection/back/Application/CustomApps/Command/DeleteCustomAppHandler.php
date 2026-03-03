<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\CustomApps\Command;

use Akeneo\Connectivity\Connection\Domain\CustomApps\Persistence\DeleteCustomAppQueryInterface;
use Akeneo\Connectivity\Connection\Domain\CustomApps\Persistence\GetCustomAppQueryInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DeleteCustomAppHandler
{
    public function __construct(
        private readonly GetCustomAppQueryInterface $getCustomAppQuery,
        private readonly DeleteCustomAppQueryInterface $deleteCustomAppQuery,
    ) {
    }

    public function handle(DeleteCustomAppCommand $customAppCommand): void
    {
        $customAppId = $customAppCommand->customAppId;

        $customAppData = $this->getCustomAppQuery->execute($customAppId);
        if (null === $customAppData) {
            throw new \InvalidArgumentException(\sprintf('Custom app with %s client_id was not found.', $customAppId));
        }

        $this->deleteCustomAppQuery->execute($customAppId);
    }
}
