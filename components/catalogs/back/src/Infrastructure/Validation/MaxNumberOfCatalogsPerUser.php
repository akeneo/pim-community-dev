<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Validation;

use Symfony\Component\Validator\Constraint;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
#[\Attribute]
final class MaxNumberOfCatalogsPerUser extends Constraint
{
    public string $message = 'akeneo_catalogs.validation.max_number_of_catalogs_per_user_message';

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
