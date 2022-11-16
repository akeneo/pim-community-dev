<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation;

use Akeneo\Pim\Automation\IdentifierGenerator\Application\Create\CreateGeneratorCommand;
use Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation\AutoNumberLimitPerStructure;
use Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation\AutoNumberLimitPerStructureValidator;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContext;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AutoNumberLimitPerStructureValidatorSpec extends ObjectBehavior
{
    public function let(ExecutionContext $context): void
    {
        $this->initialize($context);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(AutoNumberLimitPerStructureValidator::class);
    }

    public function it_can_only_validate_the_right_constraint(): void
    {
        $this->shouldThrow(\InvalidArgumentException::class)
            ->during('validate', [[], new NotBlank()]);
    }

    public function it_should_throw_an_error_when_a_property_structure_is_not_an_array(ExecutionContext $context)
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
            ['type' => 'free_text', 'string' => 'abcdef'],
            new \stdClass(),
        ];
        $this->shouldThrow(new \InvalidArgumentException('Expected an array. Got: stdClass'))
            ->during('validate', [$structure, new AutoNumberLimitPerStructure()]);
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
            ['type' => 'free_text', 'string' => 'abcdef'],
            ['string' => 'abcdef'],
        ];
        $this->shouldThrow(new \InvalidArgumentException('Expected the key "type" to exist.'))
            ->during('validate', [$structure, new AutoNumberLimitPerStructure()]);
    }

    public function it_should_build_violation_when_auto_number_already_exist(ExecutionContext $context): void
    {
        $structure = [
            ['type' => 'free_text', 'string' => 'abcdef'],
            ['type' => 'auto_number', 'numberMin' => 3, 'digitsMin' => 2],
            ['type' => 'auto_number', 'numberMin' => 5, 'digitsMin' => 4],
        ];
        $command = new CreateGeneratorCommand(
            'generatorCode',
            [],
            $structure,
            ['fr' => 'Générateur'],
            'sku',
            '-'
        );
        $context->getRoot()
            ->shouldBeCalledOnce()
            ->willReturn($command);

        $context->buildViolation(
            'validation.create.auto_number_limit_reached',
            ['{{limit}}' => 1]
        )->shouldBeCalled();

        $this->validate($structure, new AutoNumberLimitPerStructure());
    }

    public function it_should_be_valid_when_auto_number_is_under_limit(ExecutionContext $context): void
    {
        $structure = [
            ['type' => 'free_text', 'string' => 'abcdef'],
            ['type' => 'auto_number', 'numberMin' => 3, 'digitsMin' => 2],
        ];
        $command = new CreateGeneratorCommand(
            'generatorCode',
            [],
            $structure,
            ['fr' => 'Générateur'],
            'sku',
            '-'
        );
        $context->getRoot()
            ->shouldBeCalledOnce()
            ->willReturn($command);

        $context->buildViolation(
            'validation.create.auto_number_limit_reached',
            ['{{limit}}' => 2]
        )->shouldNotBeCalled();

        $this->validate($structure, new AutoNumberLimitPerStructure());
    }
}
