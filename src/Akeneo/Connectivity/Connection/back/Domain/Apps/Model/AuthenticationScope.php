<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Apps\Model;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class AuthenticationScope
{
    public const SCOPE_OPENID = 'openid';
    public const SCOPE_PROFILE = 'profile';
    public const SCOPE_EMAIL = 'email';

    /**
     * @return array<string>
     */
    public static function getAllScopes(): array
    {
        return [self::SCOPE_OPENID, self::SCOPE_PROFILE, self::SCOPE_EMAIL];
    }
}
