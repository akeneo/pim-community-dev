<?php

namespace spec\PimEnterprise\Bundle\CatalogRuleBundle\Engine\ProductRuleApplier;

use Akeneo\Bundle\StorageUtilsBundle\Doctrine\ObjectDetacherInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Akeneo\Bundle\RuleEngineBundle\Event\RuleEvents;
use Akeneo\Bundle\RuleEngineBundle\Model\RuleInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ValidatorInterface;

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
        $this->shouldHaveType('PimEnterprise\Bundle\CatalogRuleBundle\Engine\ProductRuleApplier\ProductsValidator');
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
        $eventDispatcher->dispatch(RuleEvents::SKIP, Argument::any())->shouldNotBeCalled();

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
        $eventDispatcher->dispatch(RuleEvents::SKIP, Argument::any())->shouldBeCalled();

        $this->validate($rule, [$invalidProduct]);
    }
}
