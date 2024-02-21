<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Structure\Component\Validator\Constraints;

use Akeneo\Pim\Structure\Component\Validator\Constraints\IsVariantAxisWithoutAvailableLocales;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Validator\Constraint;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IsVariantAxisWithoutAvailableLocalesSpec extends ObjectBehavior
{
    function it_is_initialiazable()
    {
        $this->shouldBeAnInstanceOf(IsVariantAxisWithoutAvailableLocales::class);
    }

    function it_is_a_validator_constraint()
    {
        $this->shouldBeAnInstanceOf(Constraint::class);
    }

    function it_has_a_message()
    {
        $this->message->shouldBe('pim_structure.validation.available_locales.is_variant_axis_without_available_locales');
    }

    function it_has_a_property_path()
    {
        $this->propertyPath->shouldBe('available_locales');
    }

    function it_is_a_class_constraint()
    {
        $this->getTargets()->shouldReturn(Constraint::CLASS_CONSTRAINT);
    }

    function it_is_validated_by_a_specific_validator()
    {
        $this->validatedBy()->shouldReturn('pim_structure.validator.constraint.available_locales.is_variant_axis_without_available_locales');
    }
}
