<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation;

use Symfony\Component\Validator\Constraint;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class AttributeShouldHaveTypes extends Constraint
{
    /** @var array<string> */
    public array $types;

    public string $message = 'validation.identifier_generator.attribute_should_have_type';

    /**
     * @inerhitDoc
     */
    public function getTargets(): string
    {
        return self::PROPERTY_CONSTRAINT;
    }

    public function getDefaultOption(): string
    {
        return 'types';
    }

    public function getRequiredOptions(): array
    {
        return ['types'];
    }
}
