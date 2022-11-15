<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation;

use Akeneo\Pim\Automation\IdentifierGenerator\Application\Create\CreateGeneratorCommand;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\FreeText;
use Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation\AutoNumberFieldsRequired;
use Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation\AutoNumberFieldsRequiredValidator;
use Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation\FreeTextFieldsRequired;
use Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation\FreeTextFieldsRequiredValidator;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContext;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AutoNumberFieldsRequiredValidatorSpec extends ObjectBehavior
{
    public function let(ExecutionContext $context): void
    {
        $this->initialize($context);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(AutoNumberFieldsRequiredValidator::class);
    }

    public function it_can_only_validate_the_right_constraint(): void
    {
        $this->shouldThrow(\InvalidArgumentException::class)
            ->during('validate', [[], new NotBlank()]);
    }

    public function it_could_throw_an_error_when_its_not_the_right_command(ExecutionContext $context): void
    {
        $context->getRoot()
            ->shouldBeCalled()
            ->willReturn(new \stdClass());
        $this->shouldThrow(\InvalidArgumentException::class)
            ->during('validate', [[], new AutoNumberFieldsRequired()]);
    }

    public function it_should_throw_an_error_when_a_property_structure_is_not_an_array(ExecutionContext $context)
    {
        $command = new CreateGeneratorCommand(
            'generatorCode',
            [],
            ['type' => 'auto_number', 'numberMin' => 2],
            ['fr' => 'Générateur'],
            'sku',
            '-'
        );
        $context->getRoot()
            ->shouldBeCalled()
            ->willReturn($command);

        $structure = new \stdClass();
        $this->shouldThrow(new \InvalidArgumentException('Expected an array. Got: stdClass'))
            ->during('validate', [$structure, new AutoNumberFieldsRequired()]);
    }

    public function it_should_throw_an_error_when_a_property_does_not_contain_valid_key(ExecutionContext $context)
    {
        $command = new CreateGeneratorCommand(
            'generatorCode',
            [],
            ['type' => 'free_text', 'string' => 'abcdef'],
            ['fr' => 'Générateur'],
            'sku',
            '-'
        );
        $context->getRoot()
            ->shouldBeCalled()
            ->willReturn($command);

        $structure = [
            ['type' => 'auto_number', 'numberMin' => 2],
            ['numberMin' => 2],
        ];
        $this->shouldThrow(new \InvalidArgumentException('Expected the key "type" to exist.'))
            ->during('validate', [$structure, new AutoNumberFieldsRequired()]);
    }

    public function it_should_throw_an_error_when_a_property_does_not_contain_required_field(ExecutionContext $context)
    {
        $structure = ['type' => 'auto_number', 'numberMin' => 2];
        $command = new CreateGeneratorCommand(
            'generatorCode',
            [],
            [$structure],
            ['fr' => 'Générateur'],
            'sku',
            '-'
        );
        $context->getRoot()
            ->shouldBeCalled()
            ->willReturn($command);

        $context->buildViolation(
            'validation.create.auto_number_fields_required',
            [
                '{{field}}' => 'numberMin, digitsMin',
                '{{type}}' => 'auto_number',
            ]
        )->shouldBeCalled();

        $this->validate($structure, new AutoNumberFieldsRequired());
    }

    public function it_should_be_valid(ExecutionContext $context): void
    {
        $structure = ['type' => 'auto_number', 'numberMin' => 2, 'digitsMin' => 4];
        $command = new CreateGeneratorCommand(
            'generatorCode',
            [],
            [$structure],
            ['fr' => 'Générateur'],
            'sku',
            '-'
        );
        $context->getRoot()
            ->shouldBeCalledOnce()
            ->willReturn($command);

        $context->buildViolation(
            'validation.create.auto_number_fields_required',
            [
                '{{field}}' => 'numberMin, digitsMin',
                '{{type}}' => 'auto_number',
            ]
        )->shouldNotBeCalled();

        $this->validate($structure, new AutoNumberFieldsRequired());
    }
}
