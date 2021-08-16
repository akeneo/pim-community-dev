<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\Validation;

use Symfony\Component\Validator\Constraint;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ScopeMustBeValid extends Constraint
{
    public string $message = 'Scopes must be valid';

    public function getTargets(): string
    {
        return self::PROPERTY_CONSTRAINT;
    }
}
