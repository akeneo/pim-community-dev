<?php

namespace spec\PimEnterprise\Bundle\CatalogRuleBundle\EventSubscriber;

use Doctrine\ORM\EntityRepository;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\Repository\AttributeRepository;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Pim\Bundle\CatalogBundle\Model\AbstractProduct;
use PimEnterprise\Bundle\CatalogRuleBundle\Engine\ProductRuleLoader;
use PimEnterprise\Bundle\CatalogRuleBundle\Engine\ProductRuleSelector;
use PimEnterprise\Bundle\CatalogRuleBundle\Manager\RuleLinkedResourceManager;
use PimEnterprise\Bundle\CatalogRuleBundle\Model\RuleLinkedResource;
use PimEnterprise\Bundle\RuleEngineBundle\Event\RuleEvent;
use PimEnterprise\Bundle\RuleEngineBundle\Model\LoadedRule;
use PimEnterprise\Bundle\RuleEngineBundle\Model\Rule;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\GenericEvent;

class LinkedResourceSubscriberSpec extends ObjectBehavior
{
    function let(
        RuleLinkedResourceManager $linkedResManager,
        EntityRepository          $ruleLinkedResRepo,
        ProductRuleSelector       $productRuleSelector,
        ProductRuleLoader         $productRuleLoader,
        AttributeRepository       $attributeRepository
    ) {
        $this->beConstructedWith(
            $linkedResManager,
            $ruleLinkedResRepo,
            $productRuleSelector,
            $productRuleLoader,
            $attributeRepository
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

    function it_saves_a_new_rule_linked_resource(
        $productRuleLoader,
        $linkedResManager,
        $attributeRepository,
        RuleEvent $event,
        LoadedRule $loadedRule,
        AbstractAttribute $attribute1,
        AbstractAttribute $attribute2,
        Rule $rule
    ) {
        $event->getRule()->shouldBeCalled()->willReturn($rule);

        $productRuleLoader->load($rule)->shouldBeCalled()->willReturn($loadedRule);

        $loadedRule->getActions()->shouldBeCalled()->willReturn([['field' => 'name', 'to_field' => 'description']]);

        $attributeRepository->findByReference('name')->shouldBeCalled()->willReturn($attribute1);
        $attributeRepository->findByReference('description')->shouldBeCalled()->willReturn($attribute2);

        $attribute1->__toString()->willReturn('name');
        $attribute1->getId()->willReturn(42);

        $attribute2->__toString()->willReturn('description');
        $attribute2->getId()->willReturn(43);

        $linkedResManager->save(Argument::type('PimEnterprise\Bundle\CatalogRuleBundle\Model\RuleLinkedResource'))
            ->shouldBeCalledTimes(2);

        $this->saveRuleLinkedResource($event);
    }
}
