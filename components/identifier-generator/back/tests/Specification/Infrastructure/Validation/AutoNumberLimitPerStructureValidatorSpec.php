<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation;

use Akeneo\Pim\Automation\IdentifierGenerator\Application\Create\CreateGeneratorCommand;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\AutoNumber;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\FreeText;
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
        $this->shouldThrow(\InvalidArgumentException::class)->during('validate', ['code', new NotBlank()]);
    }

    public function it_could_throw_an_error_when_its_not_the_right_command(ExecutionContext $context): void
    {
        $context->getRoot()
            ->willReturn(new \stdClass());
        $this->shouldThrow(\InvalidArgumentException::class)->during('validate', ['code', new AutoNumberLimitPerStructure(['limit' => 2])]);

        $this->shouldThrow(\InvalidArgumentException::class)->during('validate', ['code', new AutoNumberLimitPerStructure()]);
    }

    public function it_should_build_violation_when_auto_number_already_exist(ExecutionContext $context): void
    {
        $structure = [
            FreeText::fromString('abcdef'),
            AutoNumber::fromValues(3, 2),
            AutoNumber::fromValues(5, 4),
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
            FreeText::fromString('abcdef'),
            AutoNumber::fromValues(3, 2),
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
        )->shouldNotBeCalled();

        $this->validate($structure, new AutoNumberLimitPerStructure());
    }
}
