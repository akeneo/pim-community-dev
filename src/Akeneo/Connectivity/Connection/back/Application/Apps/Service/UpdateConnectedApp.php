<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Apps\Service;

use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\UpdateConnectedAppQueryInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class UpdateConnectedApp implements UpdateConnectedAppInterface
{
    public function __construct(
        private UpdateConnectedAppQueryInterface $updateConnectedAppQuery,
    ) {
    }

    public function execute(array $scopes, string $appId): void
    {
        $this->updateConnectedAppQuery->execute($scopes, $appId);
    }
}
