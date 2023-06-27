<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Structure\Component\Validator\Constraints;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository\IdentifierGeneratorRepository;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Pim\Structure\Component\Validator\Constraints\IdentifierAttributeCreationLimit;
use Akeneo\Pim\Structure\Component\Validator\Constraints\IdentifierAttributeCreationLimitValidator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Context\ExecutionContext;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IdentifierAttributeCreationLimitValidatorSpec extends ObjectBehavior
{
    public function let(AttributeRepositoryInterface $repository, ExecutionContext $context): void
    {
        $this->beConstructedWith($repository);
        $this->initialize($context);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(IdentifierAttributeCreationLimitValidator::class);
    }

    public function it_is_a_constraint_validator(): void
    {
        $this->shouldHaveType(ConstraintValidator::class);
    }

    public function it_can_only_validate_the_right_constraint(): void
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('validate', ['code', new NotBlank()]);
    }

    public function it_should_build_violation_when_identifier_attribute_limit_is_reached(
        ExecutionContext $context,
        IdentifierGeneratorRepository $repository
    ): void {
        $repository
            ->findBy(Argument::any())
            ->shouldBeCalledOnce()
            ->willReturn([1, 2]);

        $context->buildViolation(
            'pim_catalog.constraint.identifier_attribute_limit_reached',
            ['{{limit}}' => 2]
        )->shouldBeCalled();

        $this->validate('identifier', new IdentifierAttributeCreationLimit(['limit' => 2]));
    }

    public function it_should_be_valid_when_identifier_attribute_is_under_limit(
        ExecutionContext $context,
        IdentifierGeneratorRepository $repository
    ): void {
        $repository
            ->findBy(Argument::any())
            ->shouldBeCalledOnce()
            ->willReturn([1]);

        $context->buildViolation((string)Argument::any())->shouldNotBeCalled();

        $this->validate('identifier', new IdentifierAttributeCreationLimit(['limit' => 2]));
    }
}
