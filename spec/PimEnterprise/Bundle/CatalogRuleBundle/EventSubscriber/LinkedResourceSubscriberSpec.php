<?php

namespace spec\PimEnterprise\Bundle\CatalogRuleBundle\EventSubscriber;

use Doctrine\ORM\EntityRepository;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Pim\Bundle\CatalogBundle\Model\AbstractProduct;
use PimEnterprise\Bundle\CatalogRuleBundle\Engine\ProductRuleLoader;
use PimEnterprise\Bundle\CatalogRuleBundle\Engine\ProductRuleSelector;
use PimEnterprise\Bundle\CatalogRuleBundle\Manager\RuleLinkedResourceManager;
use PimEnterprise\Bundle\CatalogRuleBundle\Model\RuleLinkedResource;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\GenericEvent;

class LinkedResourceSubscriberSpec extends ObjectBehavior
{
    function let(
        RuleLinkedResourceManager $linkedResManager,
        EntityRepository          $ruleLinkedResRepo,
        ProductRuleSelector       $productRuleSelector,
        ProductRuleLoader         $productRuleLoader
    ) {
        $this->beConstructedWith(
            $linkedResManager,
            $ruleLinkedResRepo,
            $productRuleSelector,
            $productRuleLoader
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Symfony\Component\EventDispatcher\EventSubscriberInterface');
    }

    function it_deletes_a_rule_linked_resource(
        $ruleLinkedResRepo,
        $linkedResManager,
        GenericEvent $event,
        AbstractAttribute $attribute,
        RuleLinkedResource $ruleLinkedResource
    ) {
        $event->getSubject()->shouldBeCalled()->willReturn($attribute);

        $attribute->getId()->willReturn(42);


        $ruleLinkedResRepo->findBy(
            Argument::any()
        )->shouldBeCalled()->willReturn([$ruleLinkedResource]);

        $linkedResManager->remove($ruleLinkedResource)->shouldBeCalled();

        $this->deleteRuleLinkedResource($event);
    }

    function it_does_not_delete_a_rule_linked_resource_when_argument_is_not_the_required_type(
        $linkedResManager,
        GenericEvent $event,
        AbstractProduct $product
    ) {
        $event->getSubject()->shouldBeCalled()->willReturn($product);
        $linkedResManager->remove(Argument::any())->shouldNotBeCalled();

        $this->deleteRuleLinkedResource($event);
    }
}
