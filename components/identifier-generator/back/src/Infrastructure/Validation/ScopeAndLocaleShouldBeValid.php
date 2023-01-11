<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Collection;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ScopeAndLocaleShouldBeValid extends Constraint
{
    /**
     * These 2 next messages are translated through Collection Symfony validator.
     * @see Collection
     */
    public string $missingField = 'This field is missing.';
    public string $notExpectedField = 'This field was not expected.';

    public string $unknownScope = 'validation.identifier_generator.unknown_scope';
    public string $unknownLocale = 'validation.identifier_generator.unknown_locale';
    public string $inactiveLocale = 'validation.identifier_generator.inactive_locale';

    /**
     * @inerhitDoc
     */
    public function getTargets(): string
    {
        return self::PROPERTY_CONSTRAINT;
    }
}
