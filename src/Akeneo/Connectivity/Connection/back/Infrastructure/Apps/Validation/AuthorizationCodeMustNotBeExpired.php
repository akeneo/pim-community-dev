<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\Validation;

use Symfony\Component\Validator\Constraint;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AuthorizationCodeMustNotBeExpired extends Constraint
{
    public string $message = 'invalid_grant';
    public string $cause = 'Code has expired';

    public function getTargets(): string
    {
        return self::PROPERTY_CONSTRAINT;
    }
}
