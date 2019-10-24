<?php
declare(strict_types=1);

namespace spec\Akeneo\Apps\Application\Validation\App;

use Akeneo\Apps\Application\Validation\App\CodeMustBeUnique;
use Akeneo\Apps\Application\Validation\App\CodeMustBeUniqueValidator;
use Akeneo\Apps\Domain\Model\Read\App;
use Akeneo\Apps\Domain\Model\ValueObject\FlowType;
use Akeneo\Apps\Domain\Persistence\Repository\AppRepository;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class CodeMustBeUniqueValidatorSpec extends ObjectBehavior
{
    public function let(AppRepository $repository, ExecutionContextInterface $context): void
    {
        $this->beConstructedWith($repository);
        $this->initialize($context);
    }

    public function it_is_a_constraint_validator(): void
    {
        $this->shouldHaveType(CodeMustBeUniqueValidator::class);
        $this->shouldImplement(ConstraintValidatorInterface::class);
    }

    public function it_validates_an_app_code_must_be_unique($repository, $context): void
    {
        $constraint = new CodeMustBeUnique();
        $repository->findOneByCode('magento')->willReturn(null);
        $context->buildViolation(Argument::any())->shouldNotBeCalled();

        $this->validate('magento', $constraint)->shouldReturn(null);
    }

    public function it_build_a_violation_if_the_code_is_not_unique(
        $repository,
        $context,
        ConstraintViolationBuilderInterface $builder
    ): void {
        $constraint = new CodeMustBeUnique();
        $repository
            ->findOneByCode('magento')
            ->willReturn(new App('1', 'magento', 'Magento connector', FlowType::DATA_DESTINATION));

        $context->buildViolation('akeneo_apps.constraint.code.must_be_unique')
            ->shouldBeCalled()
            ->willReturn($builder);
        $builder->addViolation()->shouldBeCalled();

        $this->validate('magento', $constraint)->shouldReturn(null);
    }
}
