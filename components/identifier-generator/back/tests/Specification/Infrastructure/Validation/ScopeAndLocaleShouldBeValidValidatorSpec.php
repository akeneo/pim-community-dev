<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation;

use Akeneo\Channel\Infrastructure\Component\Query\PublicApi\GetChannelCodeWithLocaleCodesInterface;
use Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation\ScopeAndLocaleShouldBeValid;
use Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation\ScopeAndLocaleShouldBeValidValidator;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ScopeAndLocaleShouldBeValidValidatorSpec extends ObjectBehavior
{
    public function let(
        GetAttributes $getAttributes,
        GetChannelCodeWithLocaleCodesInterface $getChannelCodeWithLocaleCodes,
        ExecutionContext $context
    ): void {
        $this->beConstructedWith($getAttributes, $getChannelCodeWithLocaleCodes);
        $this->initialize($context);

        $getChannelCodeWithLocaleCodes->findAll()->willReturn([
            ['channelCode' => 'ecommerce', 'localeCodes' => ['en_US']],
            ['channelCode' => 'website', 'localeCodes' => ['fr_FR']],
        ]);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(ScopeAndLocaleShouldBeValidValidator::class);
    }

    public function it_can_only_validate_the_right_constraint(): void
    {
        $this->shouldThrow(\InvalidArgumentException::class)
            ->during('validate', [
                ['type' => 'simple_select', 'operator' => 'EMPTY', 'attributeCode' => 'color'],
                new NotBlank()
            ]);
    }

    public function it_should_not_validate_something_else_than_an_array(ExecutionContext $context): void
    {
        $context->buildViolation((string)Argument::any())->shouldNotBeCalled();
        $this->validate(new \stdClass(), new ScopeAndLocaleShouldBeValid());
    }

    public function it_should_not_validate_if_there_are_no_attribute_code(ExecutionContext $context): void
    {
        $context->buildViolation((string)Argument::any())->shouldNotBeCalled();
        $this->validate(
            ['type' => 'simple_select', 'operator' => 'EMPTY'],
            new ScopeAndLocaleShouldBeValid()
        );
    }

    public function it_should_not_validate_if_attribute_does_not_exist(
        GetAttributes $getAttributes,
        ExecutionContext $context,
    ): void {
        $context->buildViolation((string)Argument::any())->shouldNotBeCalled();
        $getAttributes->forCode('color')->shouldBeCalled()->willReturn(null);

        $this->validate(
            ['type' => 'simple_select', 'operator' => 'EMPTY', 'attributeCode' => 'color'],
            new ScopeAndLocaleShouldBeValid()
        );
    }

    public function it_should_build_violation_when_scope_is_missing_for_scopable_attribute(
        GetAttributes $getAttributes,
        ExecutionContext $context,
        ConstraintViolationBuilderInterface $constraintViolationBuilder
    ): void {
        $context->buildViolation(Argument::any(), ['{{ attributeCode }}' => 'color'])->shouldBeCalled()->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->atPath('[scope]')->shouldBeCalled()->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->addViolation()->shouldBeCalled();

        $getAttributes->forCode('color')->shouldBeCalled()->willReturn($this->getColorAttribute(scopable: true));

        $this->validate(
            ['type' => 'simple_select', 'operator' => 'EMPTY', 'attributeCode' => 'color'],
            new ScopeAndLocaleShouldBeValid()
        );
    }

    public function it_should_build_violation_when_scope_is_set_for_non_scopable_attribute(
        GetAttributes $getAttributes,
        ExecutionContext $context,
        ConstraintViolationBuilderInterface $constraintViolationBuilder
    ): void {
        $context->buildViolation('This field was not expected.')->shouldBeCalled()->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->atPath('[scope]')->shouldBeCalled()->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->addViolation()->shouldBeCalled();

        $getAttributes->forCode('color')->shouldBeCalled()->willReturn($this->getColorAttribute(scopable: false));

        $this->validate(
            ['type' => 'simple_select', 'operator' => 'EMPTY', 'attributeCode' => 'color', 'scope' => 'ecommerce'],
            new ScopeAndLocaleShouldBeValid()
        );
    }

    public function it_should_build_violation_when_scope_does_not_exist(
        GetAttributes $getAttributes,
        ExecutionContext $context,
        ConstraintViolationBuilderInterface $constraintViolationBuilder
    ): void {
        $context->buildViolation(
            'validation.identifier_generator.unknown_scope',
            ['{{ scopeCode }}' => 'unknown']
        )->shouldBeCalled()->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->atPath('[scope]')->shouldBeCalled()->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->addViolation()->shouldBeCalled();

        $getAttributes->forCode('color')->shouldBeCalled()->willReturn($this->getColorAttribute(scopable: true));

        $this->validate(
            ['type' => 'simple_select', 'operator' => 'EMPTY', 'attributeCode' => 'color', 'scope' => 'unknown'],
            new ScopeAndLocaleShouldBeValid()
        );
    }

    public function it_should_build_violation_when_locale_is_missing_for_localizable_attribute(
        GetAttributes $getAttributes,
        ExecutionContext $context,
        ConstraintViolationBuilderInterface $constraintViolationBuilder
    ): void {
        $context->buildViolation(Argument::any(), ['{{ attributeCode }}' => 'color'])->shouldBeCalled()->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->atPath('[locale]')->shouldBeCalled()->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->addViolation()->shouldBeCalled();

        $getAttributes->forCode('color')->shouldBeCalled()->willReturn($this->getColorAttribute(localizable: true));

        $this->validate(
            ['type' => 'simple_select', 'operator' => 'EMPTY', 'attributeCode' => 'color'],
            new ScopeAndLocaleShouldBeValid()
        );
    }

    public function it_should_build_violation_when_locale_is_set_for_non_localizable_attribute(
        GetAttributes $getAttributes,
        ExecutionContext $context,
        ConstraintViolationBuilderInterface $constraintViolationBuilder
    ): void {
        $context->buildViolation('This field was not expected.')->shouldBeCalled()->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->atPath('[locale]')->shouldBeCalled()->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->addViolation()->shouldBeCalled();

        $getAttributes->forCode('color')->shouldBeCalled()->willReturn($this->getColorAttribute(localizable: false));

        $this->validate(
            ['type' => 'simple_select', 'operator' => 'EMPTY', 'attributeCode' => 'color', 'locale' => 'en_US'],
            new ScopeAndLocaleShouldBeValid()
        );
    }

    public function it_should_build_violation_when_locale_does_not_exist(
        GetAttributes $getAttributes,
        ExecutionContext $context,
        ConstraintViolationBuilderInterface $constraintViolationBuilder
    ): void {
        $context->buildViolation(
            'validation.identifier_generator.unknown_locale',
            ['{{ localeCode }}' => 'unknown']
        )->shouldBeCalled()->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->atPath('[locale]')->shouldBeCalled()->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->addViolation()->shouldBeCalled();

        $getAttributes->forCode('color')->shouldBeCalled()->willReturn($this->getColorAttribute(localizable: true));

        $this->validate(
            ['type' => 'simple_select', 'operator' => 'EMPTY', 'attributeCode' => 'color', 'locale' => 'unknown'],
            new ScopeAndLocaleShouldBeValid()
        );
    }

    public function it_should_build_violation_when_locale_does_not_belong_to_channel(
        GetAttributes $getAttributes,
        ExecutionContext $context,
        ConstraintViolationBuilderInterface $constraintViolationBuilder
    ): void {
        $context->buildViolation(
            'validation.identifier_generator.inactive_locale',
            ['{{ localeCode }}' => 'fr_FR', '{{ scopeCode }}' => 'ecommerce']
        )->shouldBeCalled()->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->atPath('[locale]')->shouldBeCalled()->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->addViolation()->shouldBeCalled();

        $getAttributes->forCode('color')->shouldBeCalled()->willReturn($this->getColorAttribute(localizable: true, scopable: true));

        $this->validate(
            ['type' => 'simple_select', 'operator' => 'EMPTY', 'attributeCode' => 'color', 'locale' => 'fr_FR', 'scope' => 'ecommerce'],
            new ScopeAndLocaleShouldBeValid()
        );
    }

    private function getColorAttribute(
        bool $scopable = false,
        bool $localizable = false
    ): Attribute {
        return new Attribute(
            'color',
            'pim_catalog_simpleselect',
            [],
            $localizable,
            $scopable,
            null,
            null,
            null,
            'pim_catalog_simpleselect',
            []
        );
    }
}
