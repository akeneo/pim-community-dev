<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Marketplace;

use Akeneo\Connectivity\Connection\Domain\Marketplace\DTO\GetAllAppsResult;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
interface GetAllAppsQueryInterface
{
    public function execute(): GetAllAppsResult;
}
