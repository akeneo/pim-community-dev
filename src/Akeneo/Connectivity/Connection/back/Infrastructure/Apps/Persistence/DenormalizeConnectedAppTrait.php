<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\Persistence;

use Akeneo\Connectivity\Connection\Domain\Apps\Model\ConnectedApp;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
trait DenormalizeConnectedAppTrait
{
    /**
     * @param array{
     *    id: string,
     *    name: string,
     *    scopes: string,
     *    connection_code: string,
     *    logo: string,
     *    author: string,
     *    user_group_name: string,
     *    connection_username: string,
     *    categories: string,
     *    certified: bool,
     *    partner: ?string,
     *    is_custom_app: ?bool,
     *    is_pending: ?bool,
     *    has_outdated_scopes: bool,
     * } $dataRow
     */
    private function denormalizeRow(array $dataRow): ConnectedApp
    {
        return new ConnectedApp(
            $dataRow['id'],
            $dataRow['name'],
            \json_decode($dataRow['scopes'], true, 512, JSON_THROW_ON_ERROR),
            $dataRow['connection_code'],
            $dataRow['logo'],
            $dataRow['author'],
            $dataRow['user_group_name'],
            $dataRow['connection_username'],
            \json_decode($dataRow['categories'], true, 512, JSON_THROW_ON_ERROR),
            (bool) $dataRow['certified'],
            $dataRow['partner'],
            (bool) ($dataRow['is_custom_app'] ?? false),
            (bool) ($dataRow['is_pending'] ?? false),
            (bool) $dataRow['has_outdated_scopes'],
        );
    }
}
