<?php

namespace Specification\Akeneo\Pim\Structure\Component\Remover;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\DashboardScoresProjectionRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Doctrine\Common\Collections\Collection;
use Doctrine\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Enrichment\Component\Product\ProductAndProductModel\Query\CountProductsWithFamilyInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class FamilyRemoverSpec extends ObjectBehavior
{
    function let(
        ObjectManager $objectManager,
        EventDispatcherInterface $eventDispatcher,
        CountProductsWithFamilyInterface $counter,
        DashboardScoresProjectionRepositoryInterface $dashboardScoresProjectionRepository
    )
    {
        $this->beConstructedWith($objectManager, $eventDispatcher, $counter, $dashboardScoresProjectionRepository);
    }

    function it_is_a_remover()
    {
        $this->shouldImplement(RemoverInterface::class);
    }

    function it_removes_family_with_no_product_belonging_to_it(
        $objectManager,
        $eventDispatcher,
        $counter,
        $dashboardScoresProjectionRepository,
        FamilyInterface $familyToRemove,
        Collection $familyVariants
    )
    {
        $counter->count($familyToRemove)->willReturn(0);
        $familyToRemove->getFamilyVariants()->willReturn($familyVariants);
        $familyVariants->isEmpty()->willReturn(true);
        $familyToRemove->getId()->willReturn(1);
        $familyToRemove->getCode()->willReturn('burger_family');
        $eventDispatcher->dispatch(Argument::cetera(), StorageEvents::PRE_REMOVE)->shouldBeCalled();
        $objectManager->remove($familyToRemove)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();
        $dashboardScoresProjectionRepository->delete(Argument::cetera(), $familyToRemove->getCode())->shouldBeCalled();
        $eventDispatcher->dispatch(Argument::cetera(), StorageEvents::POST_REMOVE)->shouldBeCalled();

        $this->remove($familyToRemove, []);
    }

    function it_throws_an_exception_if_the_family_has_variants(
        $objectManager,
        $dashboardScoresProjectionRepository,
        FamilyInterface $familyToRemove,
        Collection $familyVariants
    )
    {
        $familyToRemove->getFamilyVariants()->willReturn($familyVariants);
        $familyVariants->isEmpty()->willReturn(false);
        $familyToRemove->getCode()->willReturn('burger_family');
        $objectManager->remove($familyToRemove)->shouldNotBeCalled();
        $dashboardScoresProjectionRepository->delete(Argument::cetera(), $familyToRemove->getCode())->shouldNotBeCalled();

        $this->shouldThrow(\LogicException::class)->duringRemove($familyToRemove);
    }

    function it_throws_an_exception_if_the_family_has_products_belonging_to_it(
        $objectManager,
        $counter,
        $dashboardScoresProjectionRepository,
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
        $dashboardScoresProjectionRepository->delete(Argument::cetera(), $familyToRemove->getCode())->shouldNotBeCalled();

        $this->shouldThrow(\LogicException::class)->duringRemove($familyToRemove);
    }

    function it_throws_an_exception_if_it_is_not_a_family(\stdClass $notAFamily)
    {
        $this->shouldThrow(InvalidObjectException::class)->duringRemove($notAFamily);
    }
}
