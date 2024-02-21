<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Apps\Notifier;

use Akeneo\Connectivity\Connection\Domain\Apps\Model\ConnectedApp;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface AuthorizationRequestNotifierInterface
{
    public function notify(ConnectedApp $connectedApp): void;
}
