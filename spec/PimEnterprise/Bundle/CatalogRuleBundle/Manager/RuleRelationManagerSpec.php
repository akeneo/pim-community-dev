<?php

namespace spec\PimEnterprise\Bundle\CatalogRuleBundle\Manager;

use Akeneo\Bundle\RuleEngineBundle\Model\RuleInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductCopyValueActionInterface;
use PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductSetValueActionInterface;
use PimEnterprise\Bundle\CatalogRuleBundle\Model\RuleRelation;
use PimEnterprise\Bundle\CatalogRuleBundle\Repository\RuleRelationRepositoryInterface;
use Akeneo\Bundle\RuleEngineBundle\Model\RuleDefinition;
use Prophecy\Argument;

class RuleRelationManagerSpec extends ObjectBehavior
{
    function let(
        AttributeRepositoryInterface $attributeRepository,
        RuleRelationRepositoryInterface $ruleRelationRepo
    ) {
        $this->beConstructedWith($ruleRelationRepo, $attributeRepository, 'Pim\Bundle\CatalogBundle\Entity\Attribute');
    }

    function it_returns_impacted_attributes(
        $attributeRepository,
        RuleInterface $rule,
        ProductCopyValueActionInterface $action1,
        ProductSetValueActionInterface $action2,
        ProductSetValueActionInterface $action3,
        ProductSetValueActionInterface $action4,
        AbstractAttribute $attribute1,
        AbstractAttribute $attribute2
    ) {
        $rule->getActions()->willReturn([$action1, $action2, $action3, $action4]);

        $action1->getImpactedFields()->shouldBeCalled()->willReturn(['to_field']);
        $action2->getImpactedFields()->shouldBeCalled()->willReturn(['field']);
        $action3->getImpactedFields()->shouldBeCalled()->willReturn(['field']);
        $action4->getImpactedFields()->shouldBeCalled()->willReturn(['field_2']);

        $attribute1->__toString()->willReturn('attribute1');
        $attribute2->__toString()->willReturn('attribute2');

        $attributeRepository->findOneByIdentifier('to_field')->shouldBeCalled()->willReturn($attribute1);
        $attributeRepository->findOneByIdentifier('field')->shouldBeCalled()->willReturn($attribute2);
        $attributeRepository->findOneByIdentifier('field')->shouldBeCalled()->willReturn($attribute2);
        $attributeRepository->findOneByIdentifier('field_2')->shouldBeCalled()->willReturn(null);

        $this->getImpactedAttributes($rule)->shouldReturn([$attribute1, $attribute2]);
    }

    function it_throws_an_exception_during_the_check_of_the_impacts_on_a_wrong_resource()
    {
        $this->shouldThrow(new \InvalidArgumentException('The resource name "foo" can not be resolved.'))
            ->during('isResourceImpacted', [Argument::any(), 'foo']);
    }

    function it_tells_if_a_resource_is_impacted($ruleRelationRepo)
    {
        $ruleRelationRepo->isResourceImpactedByRule(10, 'Pim\Bundle\CatalogBundle\Entity\Attribute')->willReturn(true);
        $ruleRelationRepo->isResourceImpactedByRule(20, 'Pim\Bundle\CatalogBundle\Entity\Attribute')->willReturn(false);

        $this->isResourceImpacted(10, 'attribute')->shouldReturn(true);
        $this->isResourceImpacted(20, 'Pim\Bundle\CatalogBundle\Entity\Attribute')->shouldReturn(false);
    }

    function it_throws_an_exception_when_retrieving_rules_of_an_unknown_resource()
    {
        $this->shouldThrow(new \InvalidArgumentException('The resource name "foo" can not be resolved.'))
            ->during('getRulesForResource', [Argument::any(), 'foo']);
    }

    function it_retrieves_all_rules_related_to_a_resource(
        $ruleRelationRepo,
        RuleRelation $relation1,
        RuleRelation $relation2,
        RuleDefinition $definition1,
        RuleDefinition $definition2
    ) {
        $relation1->getRuleDefinition()->willReturn($definition1);
        $relation2->getRuleDefinition()->willReturn($definition2);
        $relations = [$relation1, $relation2];
        $definitions = [$definition1, $definition2];

        $ruleRelationRepo->findBy(Argument::any())->willReturn($relations);

        $this->getRulesForResource(Argument::any(), 'attribute')->shouldReturn($definitions);
        $this->getRulesForResource(Argument::any(), 'Pim\Bundle\CatalogBundle\Entity\Attribute')->shouldReturn($definitions);
    }
}
