<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Apps\Service;

use Akeneo\Connectivity\Connection\Domain\Settings\Model\Read\ConnectionWithCredentials;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface CreateConnectionInterface
{
    public function execute(string $code, string $label, string $flowType, int $clientId, int $userId): ConnectionWithCredentials;
}
