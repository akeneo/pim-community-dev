<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation;

use Akeneo\Pim\Automation\IdentifierGenerator\Application\CreateGeneratorCommand;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\FreeText;
use Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation\TargetAttributeShouldBeAnIdentifier;
use Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation\TargetAttributeShouldBeAnIdentifierValidator;
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
class TargetAttributeShouldBeAnIdentifierValidatorSpec extends ObjectBehavior
{
    public function let(GetAttributes $getAttributes, ExecutionContext $context): void
    {
        $this->beConstructedWith($getAttributes);
        $this->initialize($context);
    }

    public function it_is_initializable(GetAttributes $getAttributes): void
    {
        $this->shouldHaveType(TargetAttributeShouldBeAnIdentifierValidator::class);
    }

    public function it_can_only_validate_the_right_constraint(): void
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('validate', ['code', new NotBlank()]);
    }

    public function it_could_throw_an_error_when_its_not_the_right_command(ExecutionContext $context): void
    {
        $context->getRoot()
            ->willReturn(new \stdClass());
        $this->shouldThrow(\InvalidArgumentException::class)->during('validate', ['code', new TargetAttributeShouldBeAnIdentifier()]);
    }

    public function it_should_build_violation_when_target_attribute_is_not_an_identifier(
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
        $context->getRoot()
            ->shouldBeCalledOnce()
            ->willReturn(new CreateGeneratorCommand('2038e1c9-68ff-4833-b06f-01e42d206002', 'generatorCode', [], [], [], 'sku', '-'));

        $context->buildViolation(
            'validation.create.target_attribute_is_not_an_identifier',
            ['{{code}}' => 'sku', '{{type}}' => 'pim_catalog_text']
        )->shouldBeCalled();

        $this->validate('sku', new TargetAttributeShouldBeAnIdentifier());
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
        $command = new CreateGeneratorCommand(
            '2038e1c9-68ff-4833-b06f-01e42d206002',
            'generatorCode',
            [],
            [FreeText::fromString('abcdef')],
            ['fr' => 'Générateur'],
            'sku',
            '-'
        );
        $context->getRoot()
            ->shouldBeCalledOnce()
            ->willReturn($command);

        $context->buildViolation(Argument::any())->shouldNotBeCalled();

        $this->validate('sku', new TargetAttributeShouldBeAnIdentifier());
    }
}
