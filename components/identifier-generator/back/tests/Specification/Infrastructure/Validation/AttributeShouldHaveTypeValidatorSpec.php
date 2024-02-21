<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation;

use Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation\AttributeShouldHaveType;
use Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation\AttributeShouldHaveTypeValidator;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContext;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeShouldHaveTypeValidatorSpec extends ObjectBehavior
{
    public function let(GetAttributes $getAttributes, ExecutionContext $context): void
    {
        $this->beConstructedWith($getAttributes);
        $this->initialize($context);
    }

    public function it_is_initializable(GetAttributes $getAttributes): void
    {
        $this->shouldHaveType(AttributeShouldHaveTypeValidator::class);
    }

    public function it_can_only_validate_the_right_constraint(): void
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('validate', ['code', new NotBlank()]);
    }

    public function it_should_build_violation_when_attribute_should_have_type(
        GetAttributes $getAttributes,
        ExecutionContext $context
    ): void {
        $getAttributes
            ->forCode('sku')
            ->shouldBeCalledOnce()
            ->willReturn(new Attribute(
                'sku',
                AttributeTypes::TEXT,
                [],
                false,
                false,
                null,
                null,
                null,
                '',
                []
            ));

        $context->buildViolation(
            'validation.identifier_generator.attribute_should_have_type',
            ['{{ code }}' => 'sku', '{{ type }}' => 'pim_catalog_text', '{{ expected }}' => 'pim_catalog_identifier']
        )->shouldBeCalled();

        $this->validate('sku', new AttributeShouldHaveType(['type' => 'pim_catalog_identifier']));
    }

    public function it_should_be_valid_when_target_attribute_is_an_identifier(
        GetAttributes $getAttributes,
        ExecutionContext $context
    ): void {
        $getAttributes
            ->forCode('sku')
            ->shouldBeCalledOnce()
            ->willReturn(new Attribute(
                'sku',
                AttributeTypes::IDENTIFIER,
                [],
                false,
                false,
                null,
                null,
                null,
                '',
                []
            ));

        $context->buildViolation((string)Argument::any())->shouldNotBeCalled();

        $this->validate('sku', new AttributeShouldHaveType(['type' => 'pim_catalog_identifier']));
    }
}
