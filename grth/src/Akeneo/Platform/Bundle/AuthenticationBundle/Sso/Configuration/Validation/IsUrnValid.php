<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\AuthenticationBundle\Sso\Configuration\Validation;

use Symfony\Component\Validator\Constraint;

/**
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
final class IsUrnValid extends Constraint
{
    public $invalidUrn = 'This is not a valid URN.';
}
