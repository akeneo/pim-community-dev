<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Doctrine\Common\Saver;

use Akeneo\Pim\Enrichment\Component\Product\Query\GetGroupProductIdentifiers;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Detacher\BulkObjectDetacherInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SavingOptionsResolverInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\GroupType;
use Akeneo\Tool\Bundle\VersioningBundle\Manager\VersionContext;
use Akeneo\Pim\Enrichment\Component\Product\Model\GroupInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class GroupSaverSpec extends ObjectBehavior
{
    function let(
        ObjectManager $objectManager,
        SavingOptionsResolverInterface $optionsResolver,
        VersionContext $versionContext,
        EventDispatcherInterface $eventDispatcher,
        ProductQueryBuilderFactoryInterface $pqbFactory,
        BulkObjectDetacherInterface $detacher,
        GetGroupProductIdentifiers $getGroupProductIdentifiers,
        EntityManager $entityManager
    ) {
        $this->beConstructedWith(
            $objectManager,
            $versionContext,
            $optionsResolver,
            $eventDispatcher,
            $detacher,
            'Pim\Bundle\CatalogBundle\Model');
    }

    function it_is_a_saver()
    {
        $this->shouldHaveType(SaverInterface::class);
    }

    function it_saves_a_group_and_flushes_by_default(
        $objectManager,
        $optionsResolver,
        $eventDispatcher,
        GroupInterface $group,
        GroupType $type
    ) {
        $optionsResolver->resolveSaveOptions([])->willReturn(
            [
                'flush'                   => true,
                'copy_values_to_products' => false,
            ]
        );

        $group->getType()->willReturn($type);
        $group->getCode()->willReturn('my_code');
        $group->getId()->willReturn(1);

        $objectManager->persist($group)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();

        $eventDispatcher->dispatch(Argument::cetera(), StorageEvents::PRE_SAVE)->shouldBeCalled();
        $eventDispatcher->dispatch(Argument::cetera(), StorageEvents::POST_SAVE)->shouldBeCalled();
        $this->save($group);
    }

    function it_throws_exception_when_save_anything_else_than_a_group()
    {
        $anythingElse = new \stdClass();
        $this
            ->shouldThrow(
                new \InvalidArgumentException(
                    sprintf(
                        'Expects a "Akeneo\Pim\Enrichment\Component\Product\Model\GroupInterface", "%s" provided.',
                        get_class($anythingElse)
                    )
                )
            )
            ->during('save', [$anythingElse]);
    }
}
