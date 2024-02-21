<?php

namespace spec\Akeneo\Tool\Component\StorageUtils\Validator\Constraints;

use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Pim\Structure\Component\Model\Family;
use Akeneo\Tool\Component\StorageUtils\Validator\Constraints\ImmutableValidator;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\UnitOfWork;
use PhpSpec\ObjectBehavior;
use Akeneo\Tool\Component\StorageUtils\Validator\Constraints\Immutable;
use Prophecy\Argument;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class ImmutableValidatorSpec extends ObjectBehavior
{
    function let(EntityManager $entityManager, ExecutionContextInterface $context)
    {
        $this->beConstructedWith($entityManager);
        $this->initialize($context);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ImmutableValidator::class);
    }

    function it_adds_violation_when_an_immutable_property_has_been_modified(
        $context,
        $entityManager,
        UnitOfWork $unitOfWork,
        Immutable $constraint,
        ConstraintViolationBuilderInterface $violation
    ) {
        $family = new Family();
        $family->setCode('myUpdatedCode');

        $entityManager->getUnitOfWork()->willReturn($unitOfWork);
        $unitOfWork->getOriginalEntityData($family)->willReturn(['code' => 'MyOriginalCode']);

        $context->buildViolation('This property cannot be changed.')
            ->shouldBeCalled()
            ->willReturn($violation);
        $violation->atPath('code')->willReturn($violation);
        $violation->addViolation()->shouldBeCalled();

        $constraint->properties = ['code'];
        $this->validate($family, $constraint);
    }

    function it_adds_violation_when_an_immutable_reference_data_name_has_been_modified(
        $context,
        $entityManager,
        UnitOfWork $unitOfWork,
        Immutable $constraint,
        ConstraintViolationBuilderInterface $violation
    ) {
        $attribute = new Attribute();
        $attribute->setReferenceDataName('myUpdatedReferenceDataName');

        $entityManager->getUnitOfWork()->willReturn($unitOfWork);
        $unitOfWork->getOriginalEntityData($attribute)->willReturn(
            ['properties' => ['reference_data_name' => 'MyOriginalReferenceDataName']]
        );

        $context->buildViolation('This property cannot be changed.')
            ->shouldBeCalled()
            ->willReturn($violation);
        $violation->atPath('reference_data_name')->willReturn($violation);
        $violation->addViolation()->shouldBeCalled();

        $constraint->properties = ['reference_data_name'];
        $this->validate($attribute, $constraint);
    }

    function it_does_not_add_violation_when_a_immutable_property_has_not_been_modified(
        $context,
        $entityManager,
        UnitOfWork $unitOfWork,
        Immutable $constraint
    ) {
        $family = new Family();
        $family->setCode('MyOriginalCode');

        $entityManager->getUnitOfWork()->willReturn($unitOfWork);
        $unitOfWork->getOriginalEntityData($family)->willReturn(['code' => 'MyOriginalCode']);

        $context->buildViolation(Argument::any(), Argument::any())
            ->shouldNotBeCalled();

        $constraint->properties = ['code'];
        $this->validate($family, $constraint);
    }
}
