<?php

namespace spec\PimEnterprise\Bundle\CatalogRuleBundle\EventSubscriber;

use Doctrine\ORM\EntityRepository;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use PimEnterprise\Bundle\CatalogRuleBundle\Engine\ProductRuleBuilder;
use PimEnterprise\Bundle\CatalogRuleBundle\Manager\RuleRelationManager;
use PimEnterprise\Bundle\CatalogRuleBundle\Model\RuleRelationInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Event\RuleEvent;
use PimEnterprise\Bundle\RuleEngineBundle\Model\Rule;
use PimEnterprise\Bundle\RuleEngineBundle\Model\RuleDefinitionInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\GenericEvent;

class RuleRelationSubscriberSpec extends ObjectBehavior
{
    function let(
        RuleRelationManager $ruleRelationManager,
        EntityRepository $ruleRelationRepo,
        ProductRuleBuilder $productRuleBuilder
    ) {
        $this->beConstructedWith(
            $ruleRelationManager,
            $ruleRelationRepo,
            $productRuleBuilder,
            'PimEnterprise\Bundle\CatalogRuleBundle\Model\RuleRelation'
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Symfony\Component\EventDispatcher\EventSubscriberInterface');
    }

    function it_deletes_a_rule_relation(
        $ruleRelationRepo,
        $ruleRelationManager,
        GenericEvent $event,
        AttributeInterface $attribute,
        RuleRelationInterface $ruleRelation
    ) {
        $event->getSubject()->shouldBeCalled()->willReturn($attribute);

        $attribute->getId()->willReturn(42);


        $ruleRelationRepo->findBy(
            Argument::any()
        )->shouldBeCalled()->willReturn([$ruleRelation]);

        $ruleRelationManager->remove($ruleRelation)->shouldBeCalled();

        $this->removeAttribute($event);
    }

    function it_does_not_delete_a_rule_relation_when_argument_is_not_the_required_type(
        $ruleRelationManager,
        GenericEvent $event,
        ProductInterface $product
    ) {
        $event->getSubject()->shouldBeCalled()->willReturn($product);
        $ruleRelationManager->remove(Argument::any())->shouldNotBeCalled();

        $this->removeAttribute($event);
    }

    function it_saves_a_new_rule_relation(
        $productRuleBuilder,
        $ruleRelationManager,
        $ruleRelationRepo,
        RuleEvent $event,
        Rule $rule,
        AbstractAttribute $attribute1,
        AbstractAttribute $attribute2,
        RuleDefinitionInterface $definition,
        RuleRelationInterface $oldResource1,
        RuleRelationInterface $oldResource2
    ) {
        $event->getDefinition()->shouldBeCalled()->willReturn($definition);
        $definition->getId()->willReturn(42);

        // delete old resources
        $ruleRelationRepo->findBy(['rule' => 42])->willReturn([$oldResource1, $oldResource2]);
        $ruleRelationManager->remove($oldResource1)->shouldBeCalled();
        $ruleRelationManager->remove($oldResource2)->shouldBeCalled();

        // add new resources
        $productRuleBuilder->build($definition)->shouldBeCalled()->willReturn($rule);

        $rule->getActions()->shouldBeCalled()->willReturn([['field' => 'name', 'to_field' => 'description']]);

        $ruleRelationManager->getImpactedAttributes([['field' => 'name', 'to_field' => 'description']])
            ->shouldBeCalled()->willReturn([$attribute1, $attribute2]);

        $attribute1->__toString()->willReturn('name');
        $attribute1->getId()->willReturn(42);

        $attribute2->__toString()->willReturn('description');
        $attribute2->getId()->willReturn(43);

        $ruleRelationManager->save(Argument::type('PimEnterprise\Bundle\CatalogRuleBundle\Model\RuleRelation'))
            ->shouldBeCalledTimes(2);

        $this->saveRule($event);
    }
}
