<?php

namespace spec\PimEnterprise\Bundle\CatalogRuleBundle\Manager;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AbstractProduct;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Model\Rule;
use Prophecy\Argument;

class RuleManagerSpec extends ObjectBehavior
{
    function let(
        RuleRepositoryInterface $repository,
        ObjectManager $objectManager,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->beConstructedWith($repository, $objectManager, $eventDispatcher);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Resource\Model\SaverInterface');
        $this->shouldHaveType('Pim\Component\Resource\Model\RemoverInterface');
    }

    function it_saves_a_rule_object($entityManager, Rule $rule)
    {
        $entityManager->persist($rule)->shouldBeCalled();
        $entityManager->flush()->shouldBeCalled();

        $this->save($rule);
    }

    function it_throws_an_exception_if_object_is_not_a_rule_on_save(
        $entityManager,
        ProductInterface $productInterface
    ) {
        $entityManager->persist($productInterface)->shouldNotBeCalled();
        $entityManager->flush()->shouldNotBeCalled();

        $this->shouldThrow('\InvalidArgumentException')->during('save', [$productInterface]);
    }

    function it_removes_a_rule_object($entityManager, Rule $rule)
    {
        $entityManager->remove($rule)->shouldBeCalled();
        $entityManager->flush()->shouldBeCalled();

        $this->remove($rule);
    }

    function it_removes_a_rule_object_and_does_not_flush(
        $entityManager,
        Rule $rule
    ) {
        $entityManager->remove($rule)->shouldBeCalled();
        $entityManager->flush()->shouldNotBeCalled();

        $this->remove($rule, ['flush' => false]);
    }

    function it_throws_an_exception_if_object_is_not_a_rule_on_remove(
        $entityManager,
        AbstractProduct $abstractProduct
    ) {
        $entityManager->remove($abstractProduct)->shouldNotBeCalled();
        $entityManager->flush()->shouldNotBeCalled();

        $this->shouldThrow('\InvalidArgumentException')->during('remove', [$abstractProduct]);
    }
}
