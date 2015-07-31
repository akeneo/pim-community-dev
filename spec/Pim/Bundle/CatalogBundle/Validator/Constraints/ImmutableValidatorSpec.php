<?php

namespace spec\Pim\Bundle\CatalogBundle\Validator\Constraints;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\UnitOfWork;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\Family;
use Pim\Bundle\CatalogBundle\Validator\Constraints\Immutable;
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
        $this->shouldHaveType('Pim\Bundle\CatalogBundle\Validator\Constraints\ImmutableValidator');
    }

    function it_adds_violation_when_a_immutable_property_has_been_modified(
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
