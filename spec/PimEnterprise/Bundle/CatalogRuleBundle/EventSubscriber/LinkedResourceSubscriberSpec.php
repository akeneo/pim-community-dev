<?php

namespace spec\PimEnterprise\Bundle\CatalogRuleBundle\EventSubscriber;

use Doctrine\ORM\EntityRepository;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use PimEnterprise\Bundle\CatalogRuleBundle\Engine\ProductRuleBuilder;
use PimEnterprise\Bundle\CatalogRuleBundle\Manager\RuleLinkedResourceManager;
use PimEnterprise\Bundle\CatalogRuleBundle\Model\RuleLinkedResourceInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Event\RuleEvent;
use PimEnterprise\Bundle\RuleEngineBundle\Model\Rule;
use PimEnterprise\Bundle\RuleEngineBundle\Model\RuleDefinitionInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\GenericEvent;

class LinkedResourceSubscriberSpec extends ObjectBehavior
{
    function let(
        RuleLinkedResourceManager $linkedResManager,
        EntityRepository          $ruleLinkedResRepo,
        ProductRuleBuilder        $productRuleBuilder
    ) {
        $this->beConstructedWith(
            $linkedResManager,
            $ruleLinkedResRepo,
            $productRuleBuilder,
            'PimEnterprise\Bundle\CatalogRuleBundle\Model\RuleLinkedResource'
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
        AttributeInterface $attribute,
        RuleLinkedResourceInterface $ruleLinkedResource
    ) {
        $event->getSubject()->shouldBeCalled()->willReturn($attribute);

        $attribute->getId()->willReturn(42);


        $ruleLinkedResRepo->findBy(
            Argument::any()
        )->shouldBeCalled()->willReturn([$ruleLinkedResource]);

        $linkedResManager->remove($ruleLinkedResource)->shouldBeCalled();

        $this->removeAttribute($event);
    }

    function it_does_not_delete_a_rule_linked_resource_when_argument_is_not_the_required_type(
        $linkedResManager,
        GenericEvent $event,
        ProductInterface $product
    ) {
        $event->getSubject()->shouldBeCalled()->willReturn($product);
        $linkedResManager->remove(Argument::any())->shouldNotBeCalled();

        $this->removeAttribute($event);
    }

    function it_saves_a_new_rule_linked_resource(
        $productRuleBuilder,
        $linkedResManager,
        $ruleLinkedResRepo,
        RuleEvent $event,
        Rule $rule,
        AbstractAttribute $attribute1,
        AbstractAttribute $attribute2,
        RuleDefinitionInterface $definition,
        RuleLinkedResourceInterface $oldResource1,
        RuleLinkedResourceInterface $oldResource2
    ) {
        $event->getDefinition()->shouldBeCalled()->willReturn($definition);
        $definition->getId()->willReturn(42);

        // delete old resources
        $ruleLinkedResRepo->findBy(['rule' => 42])->willReturn([$oldResource1, $oldResource2]);
        $linkedResManager->remove($oldResource1)->shouldBeCalled();
        $linkedResManager->remove($oldResource2)->shouldBeCalled();

        // add new resources
        $productRuleBuilder->build($definition)->shouldBeCalled()->willReturn($rule);

        $rule->getActions()->shouldBeCalled()->willReturn([['field' => 'name', 'to_field' => 'description']]);

        $linkedResManager->getImpactedAttributes([['field' => 'name', 'to_field' => 'description']])
            ->shouldBeCalled()->willReturn([$attribute1, $attribute2]);

        $attribute1->__toString()->willReturn('name');
        $attribute1->getId()->willReturn(42);

        $attribute2->__toString()->willReturn('description');
        $attribute2->getId()->willReturn(43);

        $linkedResManager->save(Argument::type('PimEnterprise\Bundle\CatalogRuleBundle\Model\RuleLinkedResource'))
            ->shouldBeCalledTimes(2);

        $this->saveRule($event);
    }
}
