<?php

namespace Specification\Akeneo\Pim\Automation\RuleEngine\Component\Engine\ProductRuleApplier;

use Akeneo\Pim\Automation\RuleEngine\Component\Engine\ProductRuleApplier\ProductsValidator;
use Akeneo\Tool\Bundle\RuleEngineBundle\Event\RuleEvents;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleInterface;
use Akeneo\Tool\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProductsValidatorSpec extends ObjectBehavior
{
    function let(
        ValidatorInterface $productValidator,
        EventDispatcherInterface $eventDispatcher,
        ObjectDetacherInterface $objectDetacher
    ) {
        $this->beConstructedWith(
            $productValidator,
            $eventDispatcher,
            $objectDetacher
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProductsValidator::class);
    }

    function it_validates_a_product(
        $productValidator,
        $eventDispatcher,
        $objectDetacher,
        RuleInterface $rule,
        ProductInterface $validProduct,
        ConstraintViolationList $emptyViolationList
    ) {
        $rule->getCode()->willReturn('rule_one');
        $productValidator->validate($validProduct)->shouldBeCalled()->willReturn($emptyViolationList);
        $emptyViolationList->count()->willReturn(0);
        $objectDetacher->detach($validProduct)->shouldNotBeCalled();
        $eventDispatcher->dispatch(Argument::any(), RuleEvents::SKIP)->shouldNotBeCalled();

        $this->validate($rule, [$validProduct]);
    }

    function it_dispatch_event_when_encounter_invalid_product(
        $productValidator,
        $eventDispatcher,
        $objectDetacher,
        RuleInterface $rule,
        ProductInterface $invalidProduct,
        ConstraintViolationList $notEmptyViolationList
    ) {
        $rule->getCode()->willReturn('rule_one');
        $productValidator->validate($invalidProduct)->shouldBeCalled()->willReturn($notEmptyViolationList);
        $notEmptyViolationList->count()->willReturn(1);
        $notEmptyViolationList->getIterator()->willReturn(new \ArrayIterator([]));
        $objectDetacher->detach($invalidProduct)->shouldBeCalled();
        $eventDispatcher->dispatch(Argument::any(), RuleEvents::SKIP)->shouldBeCalled();

        $this->validate($rule, [$invalidProduct]);
    }
}
