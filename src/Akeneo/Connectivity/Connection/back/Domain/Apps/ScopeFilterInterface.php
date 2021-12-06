<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Apps;

use Akeneo\Connectivity\Connection\Domain\Apps\ValueObject\ScopeList;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ScopeFilterInterface
{
    public function filterAllowedScopes(ScopeList $requestedScopes): ScopeList;

    public function filterAuthorizationScopes(ScopeList $scopes): ScopeList;

    public function filterAuthenticationScopes(ScopeList $scopes): ScopeList;
}
