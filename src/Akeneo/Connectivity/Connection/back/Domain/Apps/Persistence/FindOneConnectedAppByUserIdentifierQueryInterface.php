<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Apps\Persistence;

use Akeneo\Connectivity\Connection\Domain\Apps\Model\ConnectedApp;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface FindOneConnectedAppByUserIdentifierQueryInterface
{
    public function execute(string $userIdentifier): ?ConnectedApp;
}
