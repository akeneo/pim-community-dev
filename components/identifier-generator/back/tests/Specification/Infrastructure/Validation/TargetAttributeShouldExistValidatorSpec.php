<?php

namespace Specification\Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation;

use Akeneo\Pim\Automation\IdentifierGenerator\Application\CreateGeneratorCommand;
use Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation\TargetAttributeShouldExist;
use Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation\TargetAttributeShouldExistValidator;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContext;

class TargetAttributeShouldExistValidatorSpec extends ObjectBehavior
{
    public function let(GetAttributes $getAttributes, ExecutionContext $context): void
    {
        $this->beConstructedWith($getAttributes);
        $this->initialize($context);
    }

    public function it_is_initializable(GetAttributes $getAttributes): void
    {
        $this->shouldHaveType(TargetAttributeShouldExistValidator::class);
    }

    public function it_can_only_validate_the_right_constraint(): void
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('validate', ['code', new NotBlank()]);
    }

    public function it_could_throw_an_error_when_its_not_the_right_command(ExecutionContext $context): void
    {
        $context->getRoot()
            ->willReturn(new \stdClass());
        $this->shouldThrow(\InvalidArgumentException::class)->during('validate', ['code', new TargetAttributeShouldExist()]);
    }

    public function it_should_build_violation_when_target_attribute_does_not_exist(ExecutionContext $context): void
    {
        $context->getRoot()
            ->shouldBeCalledOnce()
            ->willReturn(new CreateGeneratorCommand('2038e1c9-68ff-4833-b06f-01e42d206002', 'generatorCode', [], [], [], 'sku', '-'));

        $context->buildViolation(
            'validation.create.target_attribute_does_not_exist',
            ['{{code}}' => 'sku']
        )->shouldBeCalled();

        $this->validate('sku', new TargetAttributeShouldExist());
    }

    public function it_should_be_valid_when_target_attribute_exist(
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
        $context->getRoot()
            ->shouldBeCalledOnce()
            ->willReturn(new CreateGeneratorCommand('2038e1c9-68ff-4833-b06f-01e42d206002', 'generatorCode', [], [], [], 'sku', '-'));

        $context->buildViolation(Argument::any())->shouldNotBeCalled();

        $this->validate('sku', new TargetAttributeShouldExist());
    }
}
