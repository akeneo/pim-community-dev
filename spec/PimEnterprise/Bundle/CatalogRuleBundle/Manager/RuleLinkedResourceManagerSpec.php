<?php

namespace spec\PimEnterprise\Bundle\CatalogRuleBundle\Manager;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\Repository\AttributeRepository;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Pim\Bundle\CatalogBundle\Model\AbstractProduct;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductCopyValueActionInterface;
use PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductSetValueActionInterface;
use PimEnterprise\Bundle\CatalogRuleBundle\Model\RuleLinkedResource;
use Prophecy\Argument;

class RuleLinkedResourceManagerSpec extends ObjectBehavior
{
    function let(
        EntityManager $entityManager,
        AttributeRepository $attributeRepository,
        EntityRepository $ruleLinkedResRepo
    ) {
        $this->beConstructedWith($entityManager, $attributeRepository, $ruleLinkedResRepo, 'AttributeClass');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Akeneo\Component\Persistence\SaverInterface');
        $this->shouldHaveType('Akeneo\Component\Persistence\RemoverInterface');
    }

    function it_saves_a_rule_linked_resource_object($entityManager, RuleLinkedResource $ruleLinkedResource)
    {
        $entityManager->persist($ruleLinkedResource)->shouldBeCalled();
        $entityManager->flush()->shouldBeCalled();

        $this->save($ruleLinkedResource);
    }

    function it_throws_an_exception_if_object_is_not_a_rule_linked_resource_on_save(
        $entityManager,
        ProductInterface $productInterface
    ) {
        $entityManager->persist($productInterface)->shouldNotBeCalled();
        $entityManager->flush()->shouldNotBeCalled();

        $this->shouldThrow('\InvalidArgumentException')->during('save', [$productInterface]);
    }

    function it_removes_a_rule_linked_resource_object($entityManager, RuleLinkedResource $ruleLinkedResource)
    {
        $entityManager->remove($ruleLinkedResource)->shouldBeCalled();
        $entityManager->flush()->shouldNotBeCalled();

        $this->remove($ruleLinkedResource);
    }

    function it_throws_an_exception_if_object_is_not_a_rule_linked_resource_on_remove(
        $entityManager,
        AbstractProduct $abstractProduct
    ) {
        $entityManager->remove($abstractProduct)->shouldNotBeCalled();
        $entityManager->flush()->shouldNotBeCalled();

        $this->shouldThrow('\InvalidArgumentException')->during('remove', [$abstractProduct]);
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
