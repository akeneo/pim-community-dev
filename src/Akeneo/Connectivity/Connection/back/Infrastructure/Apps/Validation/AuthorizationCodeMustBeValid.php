<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\Validation;

use Symfony\Component\Validator\Constraint;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AuthorizationCodeMustBeValid extends Constraint
{
    public string $message = 'invalid_grant';
    public string $cause = 'Code is not valid';

    public function getTargets(): string
    {
        return self::PROPERTY_CONSTRAINT;
    }
}
