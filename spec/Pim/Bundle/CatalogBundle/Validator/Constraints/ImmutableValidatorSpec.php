<?php

namespace spec\Pim\Bundle\CatalogBundle\Validator\Constraints;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\UnitOfWork;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\Family;
use Pim\Bundle\CatalogBundle\Validator\Constraints\Immutable;
use Symfony\Component\Validator\ExecutionContextInterface;
use Prophecy\Argument;

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
        Immutable $constraint
    ) {
        $family = new Family();
        $family->setCode('myUpdatedCode');

        $entityManager->getUnitOfWork()->willReturn($unitOfWork);
        $unitOfWork->getOriginalEntityData($family)->willReturn(['code' => 'MyOriginalCode']);

        $context->addViolationAt('code', 'This property cannot be changed.')
            ->shouldBeCalled();

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

        $context->addViolationAt(Argument::any(), Argument::any())
            ->shouldNotBeCalled();

        $constraint->properties = ['code'];
        $this->validate($family, $constraint);
    }
}
