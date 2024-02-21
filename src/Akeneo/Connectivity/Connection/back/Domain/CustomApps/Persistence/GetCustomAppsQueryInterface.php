<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\CustomApps\Persistence;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @phpstan-type ExternalCustomApp array{
 *      client_id: string,
 *      name: string,
 *      activate_url: string,
 *      callback_url: string,
 * }
 */
interface GetCustomAppsQueryInterface
{
    /**
     * @return array<ExternalCustomApp>
     */
    public function execute(int $userId): array;
}
