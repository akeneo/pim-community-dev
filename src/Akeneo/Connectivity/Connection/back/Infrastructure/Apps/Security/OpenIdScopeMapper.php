<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\Security;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class OpenIdScopeMapper implements ScopeMapperInterface
{
    public const SCOPE_OPENID = 'openid';
    public const SCOPE_PROFILE = 'profile';
    public const SCOPE_EMAIL = 'email';

    public function getAuthorizationScopes(): array
    {
        return [];
    }

    public function getAuthenticationScopes(): array
    {
        return [self::SCOPE_OPENID, self::SCOPE_PROFILE, self::SCOPE_EMAIL];
    }

    public function getAcls(string $scopeName): array
    {
        return [];
    }

    public function getMessage(string $scopeName): array
    {
        // @TODO add message
        return [
            'icon' => '',
            'type' => '',
            'entities' => ''
        ];
    }

    public function getLowerHierarchyScopes(string $scopeName): array
    {
        return [];
    }
}
