<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Apps\Service;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface UpdateConnectedAppRoleWithScopesInterface
{
    /**
     * @param string[] $scopes
     */
    public function execute(string $appId, array $scopes): void;
}
