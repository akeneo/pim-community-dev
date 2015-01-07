<?php

namespace spec\PimEnterprise\Bundle\CatalogRuleBundle\Manager;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\Repository\AttributeRepository;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductCopyValueActionInterface;
use PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductSetValueActionInterface;
use PimEnterprise\Bundle\CatalogRuleBundle\Repository\RuleRelationRepositoryInterface;
use Prophecy\Argument;

class RuleRelationManagerSpec extends ObjectBehavior
{
    function let(
        EntityManager $entityManager,
        AttributeRepository $attributeRepository,
        RuleRelationRepositoryInterface $ruleRelationRepo
    ) {
        $this->beConstructedWith($entityManager, $attributeRepository, $ruleRelationRepo, 'AttributeClass');
    }

    function it_returns_impacted_attributes(
        $attributeRepository,
        ProductCopyValueActionInterface $action1,
        ProductSetValueActionInterface $action2,
        ProductSetValueActionInterface $action3,
        ProductSetValueActionInterface $action4,
        AbstractAttribute $attribute1,
        AbstractAttribute $attribute2
    ) {
        $actions = [$action1, $action2, $action3, $action4];

        $action1->getToField()->shouldBeCalled()->willReturn('to_field');
        $action2->getField()->shouldBeCalled()->willReturn('field');
        $action3->getField()->shouldBeCalled()->willReturn('field');
        $action4->getField()->shouldBeCalled()->willReturn('field_2');

        $attribute1->__toString()->willReturn('attribute1');
        $attribute2->__toString()->willReturn('attribute2');

        $attributeRepository->findByReference('to_field')->shouldBeCalled()->willReturn($attribute1);
        $attributeRepository->findByReference('field')->shouldBeCalled()->willReturn($attribute2);
        $attributeRepository->findByReference('field')->shouldBeCalled()->willReturn($attribute2);
        $attributeRepository->findByReference('field_2')->shouldBeCalled()->willReturn(null);

        $this->getImpactedAttributes($actions)->shouldReturn([$attribute1, $attribute2]);
    }
}
