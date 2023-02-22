<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Validation;

use Symfony\Component\Validator\Constraint;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class ValueUserIntentsShouldHaveAnActivatedTemplate extends Constraint
{
    public string $message = 'akeneo.category.validation.upsert.value_user_intents_on_deactivated_template';
}
