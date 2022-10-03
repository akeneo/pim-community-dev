<?php

namespace Specification\Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation;

use Akeneo\Pim\Automation\IdentifierGenerator\Application\CreateGeneratorCommand;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\FreeText;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository\IdentifierGeneratorRepository;
use Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Repository\InMemoryIdentifierGeneratorRepository;
use Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation\IdentifierGeneratorCreationLimit;
use Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation\IdentifierGeneratorCreationLimitValidator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContext;

class IdentifierGeneratorCreationLimitValidatorSpec extends ObjectBehavior
{
    public function let(InMemoryIdentifierGeneratorRepository $repository, ExecutionContext $context): void
    {
        $this->beConstructedWith($repository);
        $this->initialize($context);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(IdentifierGeneratorCreationLimitValidator::class);
    }

    public function it_can_only_validate_the_right_constraint(): void
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('validate', ['code', new NotBlank()]);
    }

    public function it_could_throw_an_error_when_its_not_the_right_command(ExecutionContext $context): void
    {
        $context->getRoot()
            ->willReturn(new \stdClass());
        $this->shouldThrow(\InvalidArgumentException::class)->during('validate', ['code', new IdentifierGeneratorCreationLimit(['limit' => 2])]);

        $this->shouldThrow(\InvalidArgumentException::class)->during('validate', ['code', new IdentifierGeneratorCreationLimit()]);
    }

    public function it_should_build_violation_when_an_identifier_generator_already_exist(
        ExecutionContext $context,
        IdentifierGeneratorRepository $repository
    ): void {
        $repository
            ->count()
            ->shouldBeCalledOnce()
            ->willReturn(1);

        $command = new CreateGeneratorCommand(
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

        $context->buildViolation(
            'validation.create.identifier_limit_reached',
            ['{{limit}}' => 1]
        )->shouldBeCalled();

        $this->validate('generatorCode', new IdentifierGeneratorCreationLimit());
    }

    public function it_should_build_violation_when_identifier_generator_limit_is_reached(
        ExecutionContext $context,
        IdentifierGeneratorRepository $repository
    ): void {
        $repository
            ->count()
            ->shouldBeCalledOnce()
            ->willReturn(2);

        $command = new CreateGeneratorCommand(
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

        $context->buildViolation(
            'validation.create.identifier_limit_reached',
            ['{{limit}}' => 2]
        )->shouldBeCalled();

        $this->validate('generatorCode', new IdentifierGeneratorCreationLimit(['limit' => 2]));
    }

    public function it_should_be_valid_when_identifier_generator_is_under_limit(
        ExecutionContext $context,
        IdentifierGeneratorRepository $repository
    ): void {
        $repository
            ->count()
            ->shouldBeCalledOnce()
            ->willReturn(1);

        $command = new CreateGeneratorCommand(
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

        $this->validate('generatorCode', new IdentifierGeneratorCreationLimit(['limit' => 2]));
    }
}
