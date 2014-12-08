<?php

namespace spec\PimEnterprise\Bundle\RuleEngineBundle\Manager;

use Doctrine\ORM\EntityManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AbstractProduct;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Model\RuleInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Repository\RuleDefinitionRepositoryInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class RuleManagerSpec extends ObjectBehavior
{
    function let(
        RuleDefinitionRepositoryInterface $repository,
        EntityManager $entityManager,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->beConstructedWith(
            $repository,
            $entityManager,
            $eventDispatcher
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Resource\Model\SaverInterface');
        $this->shouldHaveType('Pim\Component\Resource\Model\RemoverInterface');
    }

    function it_saves_a_rule_object($entityManager, RuleInterface $rule)
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

    function it_removes_a_rule_object($entityManager, RuleInterface $rule)
    {
        $entityManager->remove($rule)->shouldBeCalled();
        $entityManager->flush()->shouldBeCalled();

        $this->remove($rule);
    }

    function it_removes_a_rule_object_and_does_not_flush(
        $entityManager,
        RuleInterface $rule
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
