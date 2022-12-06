<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository\IdentifierGeneratorRepository;
use Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation\IdentifierGeneratorCreationLimit;
use Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation\IdentifierGeneratorCreationLimitValidator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContext;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IdentifierGeneratorCreationLimitValidatorSpec extends ObjectBehavior
{
    public function let(IdentifierGeneratorRepository $repository, ExecutionContext $context): void
    {
        $this->beConstructedWith($repository);
        $this->initialize($context);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(IdentifierGeneratorCreationLimitValidator::class);
    }

    public function it_can_only_validate_the_right_constraint(): void
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('validate', ['code', new NotBlank()]);
    }

    public function it_should_build_violation_when_an_identifier_generator_already_exist(
        ExecutionContext $context,
        IdentifierGeneratorRepository $repository
    ): void {
        $repository
            ->count()
            ->shouldBeCalledOnce()
            ->willReturn(1);

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

        $context->buildViolation((string)Argument::any())->shouldNotBeCalled();

        $this->validate('generatorCode', new IdentifierGeneratorCreationLimit(['limit' => 2]));
    }
}
