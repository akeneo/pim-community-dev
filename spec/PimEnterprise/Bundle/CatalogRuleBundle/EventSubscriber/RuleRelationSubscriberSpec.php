<?php

namespace spec\PimEnterprise\Bundle\CatalogRuleBundle\EventSubscriber;

use Akeneo\Bundle\RuleEngineBundle\Event\RuleEvent;
use Akeneo\Bundle\RuleEngineBundle\Model\Rule;
use Akeneo\Bundle\RuleEngineBundle\Model\RuleDefinitionInterface;
use Akeneo\Component\StorageUtils\Remover\BulkRemoverInterface;
use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use PimEnterprise\Component\CatalogRule\Engine\ProductRuleBuilder;
use PimEnterprise\Bundle\CatalogRuleBundle\Manager\RuleRelationManager;
use PimEnterprise\Component\CatalogRule\Model\RuleRelationInterface;
use PimEnterprise\Component\CatalogRule\Repository\RuleRelationRepositoryInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\GenericEvent;

class RuleRelationSubscriberSpec extends ObjectBehavior
{
    function let(
        RuleRelationManager $ruleRelationManager,
        BulkSaverInterface $ruleRelationSaver,
        BulkRemoverInterface $ruleRelationRemover,
        RuleRelationRepositoryInterface $ruleRelationRepo,
        ProductRuleBuilder $productRuleBuilder
    ) {
        $this->beConstructedWith(
            $ruleRelationManager,
            $ruleRelationSaver,
            $ruleRelationRemover,
            $ruleRelationRepo,
            $productRuleBuilder,
            'PimEnterprise\Component\CatalogRule\Model\RuleRelation'
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Symfony\Component\EventDispatcher\EventSubscriberInterface');
    }

    function it_deletes_a_rule_relation(
        $ruleRelationRepo,
        $ruleRelationRemover,
        GenericEvent $event,
        AttributeInterface $attribute,
        RuleRelationInterface $ruleRelation
    ) {
        $event->getSubject()->shouldBeCalled()->willReturn($attribute);

        $attribute->getId()->willReturn(42);

        $ruleRelationRepo->findBy(
            Argument::any()
        )->shouldBeCalled()->willReturn([$ruleRelation]);

        $ruleRelationRemover->removeAll([$ruleRelation])->shouldBeCalled();

        $this->removeAttribute($event);
    }

    function it_does_not_delete_a_rule_relation_when_argument_is_not_the_required_type(
        $ruleRelationRemover,
        GenericEvent $event,
        ProductInterface $product
    ) {
        $event->getSubject()->shouldBeCalled()->willReturn($product);
        $ruleRelationRemover->removeAll(Argument::any())->shouldNotBeCalled();

        $this->removeAttribute($event);
    }

    function it_saves_a_new_rule_relation(
        $productRuleBuilder,
        $ruleRelationManager,
        $ruleRelationRemover,
        $ruleRelationSaver,
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
        $ruleRelationRemover->removeAll([$oldResource1, $oldResource2])->shouldBeCalled();

        // add new resources
        $productRuleBuilder->build($definition)->shouldBeCalled()->willReturn($rule);
        $ruleRelationManager->getImpactedElements($rule)
            ->shouldBeCalled()->willReturn([$attribute1, $attribute2]);

        $ruleRelationSaver->saveAll(Argument::any())
            ->shouldBeCalled();

        $this->saveRule($event);
    }
}
