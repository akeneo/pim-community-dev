<?php

namespace Specification\Akeneo\Pim\Structure\Component\Remover;

use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Doctrine\Common\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Pim\Enrichment\Component\Product\ProductAndProductModel\Query\CountEntityWithFamilyVariantInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class FamilyVariantRemoverSpec extends ObjectBehavior
{
    function let(
        ObjectManager $objectManager,
        EventDispatcherInterface $eventDispatcher,
        CountEntityWithFamilyVariantInterface $counter
    ) {
        $this->beConstructedWith($objectManager, $eventDispatcher, $counter);
    }

    function it_is_a_remover()
    {
        $this->shouldImplement(RemoverInterface::class);
    }

    function it_removes_family_variant_which_have_no_entity_belonging_to_it(
        $objectManager,
        $eventDispatcher,
        $counter,
        FamilyVariantInterface $familyVariantToRemove
    ) {
        $counter->belongingToFamilyVariant($familyVariantToRemove)->willReturn(0);
        $familyVariantToRemove->getId()->willReturn(1);
        $eventDispatcher->dispatch(StorageEvents::PRE_REMOVE, Argument::cetera())->shouldBeCalled();
        $objectManager->remove($familyVariantToRemove)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();
        $eventDispatcher->dispatch(StorageEvents::POST_REMOVE, Argument::cetera())->shouldBeCalled();

        $this->remove($familyVariantToRemove, []);
    }

    function it_throws_an_exception_if_the_family_variant_has_entities_belonging_to_it(
        $objectManager,
        $counter,
        FamilyVariantInterface $familyVariantToRemove
    ) {
        $counter->belongingToFamilyVariant($familyVariantToRemove)->willReturn(5);
        $familyVariantToRemove->getId()->willReturn(1);
        $familyVariantToRemove->getCode()->willReturn('family_variant');
        $objectManager->remove($familyVariantToRemove)->shouldNotBeCalled();

        $this->shouldThrow(\LogicException::class)->duringRemove($familyVariantToRemove);
    }

    function it_throws_an_exception_if_it_is_not_a_family_variant(\stdClass $notAFamilyVariant)
    {
        $this->shouldThrow(InvalidObjectException::class)->duringRemove($notAFamilyVariant);
    }
}
