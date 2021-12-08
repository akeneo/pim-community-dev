<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\Validation;

use Symfony\Component\Validator\Constraint;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ClientIdMustHaveOngoingAuthorization extends Constraint
{
    public string $message = 'Client ID must have an ongoing authorization';

    public function getTargets(): string
    {
        return self::PROPERTY_CONSTRAINT;
    }
}
