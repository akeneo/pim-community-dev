<?php

namespace Specification\Akeneo\Pim\Structure\Component\Remover;

use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Enrichment\Component\Product\ProductAndProductModel\Query\CountProductsWithFamilyInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class FamilyRemoverSpec extends ObjectBehavior
{
    function let(ObjectManager $objectManager, EventDispatcherInterface $eventDispatcher, CountProductsWithFamilyInterface $counter)
    {
        $this->beConstructedWith($objectManager, $eventDispatcher, $counter);
    }

    function it_is_a_remover()
    {
        $this->shouldImplement(RemoverInterface::class);
    }

    function it_removes_family_with_no_product_belonging_to_it(
        $objectManager,
        $eventDispatcher,
        $counter,
        FamilyInterface $familyToRemove,
        Collection $familyVariants
    )
    {
        $counter->count($familyToRemove)->willReturn(0);
        $familyToRemove->getFamilyVariants()->willReturn($familyVariants);
        $familyVariants->isEmpty()->willReturn(true);
        $familyToRemove->getId()->willReturn(1);
        $eventDispatcher->dispatch(StorageEvents::PRE_REMOVE, Argument::cetera())->shouldBeCalled();
        $objectManager->remove($familyToRemove)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();
        $eventDispatcher->dispatch(StorageEvents::POST_REMOVE, Argument::cetera())->shouldBeCalled();

        $this->remove($familyToRemove, []);
    }

    function it_throws_an_exception_if_the_family_has_variants(
        $objectManager,
        FamilyInterface $familyToRemove,
        Collection $familyVariants
    )
    {
        $familyToRemove->getFamilyVariants()->willReturn($familyVariants);
        $familyVariants->isEmpty()->willReturn(false);
        $familyToRemove->getCode()->willReturn('burger_family');
        $objectManager->remove($familyToRemove)->shouldNotBeCalled();

        $this->shouldThrow(\LogicException::class)->duringRemove($familyToRemove);
    }

    function it_throws_an_exception_if_the_family_has_products_belonging_to_it(
        $objectManager,
        $counter,
        FamilyInterface $familyToRemove,
        Collection $familyVariants
    )
    {
        $familyToRemove->getFamilyVariants()->willReturn($familyVariants);
        $familyVariants->isEmpty()->willReturn(true);
        $counter->count($familyToRemove)->willReturn(2);
        $familyToRemove->getId()->willReturn(1);
        $familyToRemove->getCode()->willReturn('burger_family');
        $objectManager->remove($familyToRemove)->shouldNotBeCalled();

        $this->shouldThrow(\LogicException::class)->duringRemove($familyToRemove);
    }

    function it_throws_an_exception_if_it_is_not_a_family(\stdClass $notAFamily)
    {
        $this->shouldThrow(InvalidObjectException::class)->duringRemove($notAFamily);
    }
}
