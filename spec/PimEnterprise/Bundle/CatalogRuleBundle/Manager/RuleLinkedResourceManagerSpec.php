<?php

namespace spec\PimEnterprise\Bundle\CatalogRuleBundle\Manager;

use Doctrine\ORM\EntityManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AbstractProduct;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use PimEnterprise\Bundle\CatalogRuleBundle\Model\RuleLinkedResource;
use Prophecy\Argument;

class RuleLinkedResourceManagerSpec extends ObjectBehavior
{
    function let(EntityManager $entityManager)
    {
        $this->beConstructedWith($entityManager);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Resource\Model\SaverInterface');
        $this->shouldHaveType('Pim\Component\Resource\Model\RemoverInterface');
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
        $entityManager->flush()->shouldBeCalled();

        $this->remove($ruleLinkedResource);
    }

    function it_removes_a_rule_linked_resource_object_and_does_not_flush(
        $entityManager,
        RuleLinkedResource $ruleLinkedResource
    ) {
        $entityManager->remove($ruleLinkedResource)->shouldBeCalled();
        $entityManager->flush()->shouldNotBeCalled();

        $this->remove($ruleLinkedResource, ['flush' => false]);
    }

    function it_throws_an_exception_if_object_is_not_a_rule_linked_resource_on_remove(
        $entityManager,
        AbstractProduct $abstractProduct
    ) {
        $entityManager->remove($abstractProduct)->shouldNotBeCalled();
        $entityManager->flush()->shouldNotBeCalled();

        $this->shouldThrow('\InvalidArgumentException')->during('remove', [$abstractProduct]);
    }
}
