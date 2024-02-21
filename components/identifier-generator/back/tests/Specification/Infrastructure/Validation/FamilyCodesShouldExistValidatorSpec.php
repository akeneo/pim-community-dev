<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation;

use Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation\FamilyCodesShouldExist;
use Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation\FamilyCodesShouldExistValidator;
use Akeneo\Pim\Structure\Family\ServiceAPI\Query\FindFamilyCodes;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyCodesShouldExistValidatorSpec extends ObjectBehavior
{
    public function let(
        FindFamilyCodes $findFamilyCodes,
        ExecutionContext $executionContext,
    ): void
    {
        $this->beConstructedWith($findFamilyCodes);
        $this->initialize($executionContext);

        $findFamilyCodes->fromQuery(Argument::any())->willReturn(['shirts']);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(FamilyCodesShouldExistValidator::class);
    }

    public function it_can_only_validate_the_right_constraint(): void
    {
        $this->shouldThrow(\InvalidArgumentException::class)
            ->during('validate', [['shirts'], new NotBlank()]);
    }

    public function it_should_not_validate_if_family_codes_is_not_an_array(
        ExecutionContext $executionContext,
    ): void {
        $condition = 'foo';
        $executionContext->buildViolation(Argument::any())->shouldNotBeCalled();
        $this->validate($condition, new FamilyCodesShouldExist());
    }

    public function it_should_not_validate_if_family_codes_is_not_an_array_of_strings(
        ExecutionContext $executionContext,
    ): void {
        $condition = ['shirts', true];

        $executionContext->buildViolation(Argument::any())->shouldNotBeCalled();
        $this->validate($condition, new FamilyCodesShouldExist());
    }

    public function it_should_not_build_violation_if_families_exist(
        ExecutionContext $executionContext,
    ): void {
        $condition = ['shirts'];

        $executionContext->buildViolation(Argument::any())->shouldNotBeCalled();
        $this->validate($condition, new FamilyCodesShouldExist());
    }

    public function it_should_build_violation_if_families_do_not_exist(
        ExecutionContext $executionContext,
        ConstraintViolationBuilderInterface $constraintViolationBuilder,
    ): void {
        $condition = ['shirts', 'unknown_family1', 'unknown_family2'];

        $executionContext
            ->buildViolation(Argument::any(), ['{{ familyCodes }}' => '"unknown_family1", "unknown_family2"'])
            ->shouldBeCalled()
            ->willReturn($constraintViolationBuilder);

        $constraintViolationBuilder->addViolation()->shouldBeCalled();
        $this->validate($condition, new FamilyCodesShouldExist());
    }
}
