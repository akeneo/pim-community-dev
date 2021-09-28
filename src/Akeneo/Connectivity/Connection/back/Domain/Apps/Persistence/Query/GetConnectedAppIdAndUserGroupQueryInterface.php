<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Apps\Persistence\Query;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GetConnectedAppIdAndUserGroupQueryInterface
{
    /**
     * @return array{appId:string, userGroup:string}|null $appIdAndUserGroup
     */
    public function execute(string $appPublicId): ?array;
}
