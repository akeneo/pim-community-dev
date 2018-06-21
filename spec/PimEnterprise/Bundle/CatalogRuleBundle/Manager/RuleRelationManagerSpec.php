<?php

namespace spec\PimEnterprise\Bundle\CatalogRuleBundle\Manager;

use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleDefinition;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleRelation;
use Akeneo\Tool\Bundle\RuleEngineBundle\Repository\RuleRelationRepositoryInterface;
use Akeneo\Tool\Component\Classification\Model\CategoryInterface;
use Akeneo\Tool\Component\Classification\Repository\CategoryRepositoryInterface;
use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AbstractAttribute;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use PimEnterprise\Component\CatalogRule\Model\ProductAddActionInterface;
use PimEnterprise\Component\CatalogRule\Model\ProductCopyActionInterface;
use PimEnterprise\Component\CatalogRule\Model\ProductSetActionInterface;
use Prophecy\Argument;

class RuleRelationManagerSpec extends ObjectBehavior
{
    function let(
        AttributeRepositoryInterface $attributeRepository,
        CategoryRepositoryInterface $categoryRepository,
        RuleRelationRepositoryInterface $ruleRelationRepo
    ) {
        $this->beConstructedWith($ruleRelationRepo, $attributeRepository, $categoryRepository, 'Akeneo\Pim\Structure\Component\Model\Attribute',
            'Akeneo\Pim\Enrichment\Component\Category\Model\Category'
        );
    }

    function it_returns_impacted_attributes(
        $attributeRepository,
        $categoryRepository,
        RuleInterface $rule,
        ProductCopyActionInterface $action1,
        ProductSetActionInterface $action2,
        ProductSetActionInterface $action3,
        ProductSetActionInterface $action4,
        ProductAddActionInterface $action5,
        AbstractAttribute $attribute1,
        AbstractAttribute $attribute2,
        CategoryInterface $tshirt,
        ArrayCollection $categories
    ) {
        $rule->getActions()->willReturn([$action1, $action2, $action3, $action4, $action5]);

        $action1->getImpactedFields()->willReturn(['to_field']);
        $action2->getImpactedFields()->willReturn(['field']);
        $action3->getImpactedFields()->willReturn(['field']);
        $action4->getImpactedFields()->willReturn(['field_2']);
        $action5->getImpactedFields()->willReturn(['categories']);
        $action5->getItems()->willReturn(['tshirt']);

        $attribute1->__toString()->willReturn('attribute1');
        $attribute2->__toString()->willReturn('attribute2');

        $attributeRepository->findOneByIdentifier('to_field')->willReturn($attribute1);
        $attributeRepository->findOneByIdentifier('field')->willReturn($attribute2);
        $attributeRepository->findOneByIdentifier('field')->willReturn($attribute2);
        $attributeRepository->findOneByIdentifier('field_2')->willReturn(null);

        $categories->toArray()->willReturn([$tshirt]);
        $categoryRepository->getCategoriesByCodes(['tshirt'])->willReturn($categories);


        $this->getImpactedElements($rule)->shouldReturn([$tshirt, $attribute1, $attribute2]);
    }

    function it_throws_an_exception_during_the_check_of_the_impacts_on_a_wrong_resource()
    {
        $this->shouldThrow(new \InvalidArgumentException('The resource name "foo" can not be resolved.'))
            ->during('isResourceImpacted', [Argument::any(), 'foo']);
    }

    function it_tells_if_a_resource_is_impacted($ruleRelationRepo)
    {
        $ruleRelationRepo->isResourceImpactedByRule(10, 'Akeneo\Pim\Structure\Component\Model\Attribute')->willReturn(true);
        $ruleRelationRepo->isResourceImpactedByRule(20, 'Akeneo\Pim\Structure\Component\Model\Attribute')->willReturn(false);

        $this->isResourceImpacted(10, 'attribute')->shouldReturn(true);
        $this->isResourceImpacted(20, 'Akeneo\Pim\Structure\Component\Model\Attribute')->shouldReturn(false);
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
        $this->getRulesForResource(Argument::any(), 'Akeneo\Pim\Structure\Component\Model\Attribute')->shouldReturn($definitions);
    }
}
