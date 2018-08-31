<?php

namespace spec\Akeneo\Tool\Bundle\VersioningBundle\Purger;

use Akeneo\Tool\Bundle\VersioningBundle\Purger\VersionPurger;
use Akeneo\Tool\Bundle\VersioningBundle\Purger\VersionPurgerInterface;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Tool\Component\StorageUtils\Detacher\BulkObjectDetacherInterface;
use Akeneo\Tool\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Tool\Component\StorageUtils\Remover\BulkRemoverInterface;
use Akeneo\Tool\Component\Versioning\Model\VersionInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Tool\Bundle\VersioningBundle\Purger\VersionPurgerAdvisorInterface;
use Akeneo\Tool\Bundle\VersioningBundle\Repository\VersionRepositoryInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class VersionPurgerSpec extends ObjectBehavior
{
    function let(
        VersionRepositoryInterface $versionRepository,
        BulkRemoverInterface $versionRemover,
        ObjectDetacherInterface $objectDetacher,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->beConstructedWith(
            $versionRepository,
            $versionRemover,
            $objectDetacher,
            $eventDispatcher
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(VersionPurger::class);
    }

    function it_implements_purger_interface()
    {
        $this->shouldImplement(VersionPurgerInterface::class);
    }

    function it_returns_the_number_of_versions_to_be_purged(
        $versionRepository,
        CursorInterface $cursor
    ) {
        $cursor->count()->willReturn(1);

        $versionRepository
            ->findPotentiallyPurgeableBy(Argument::type('array'))
            ->willReturn($cursor);

        $this->getVersionsToPurgeCount([])->shouldReturn(1);
    }

    function it_purges_the_versions_according_to_an_advisor_and_returns_the_number_of_purged_versions(
        VersionRepositoryInterface $versionRepository,
        BulkRemoverInterface $versionRemover,
        BulkObjectDetacherInterface $objectDetacher,
        VersionPurgerAdvisorInterface $advisor,
        VersionInterface $versionToBePurged,
        VersionInterface $versionNotSupported,
        VersionInterface $versionToNotPurge
    ) {
        $versionToBePurged->getResourceName()->willReturn('products');
        $versionToBePurged->getId()->willReturn(1);

        $versionNotSupported->getResourceName()->willReturn('products');
        $versionNotSupported->getId()->willReturn(2);

        $versionToNotPurge->getResourceName()->willReturn('products');
        $versionToNotPurge->getId()->willReturn(3);

        $versionRepository
            ->findPotentiallyPurgeableBy(Argument::type('array'))
            ->willReturn([$versionToBePurged, $versionNotSupported, $versionToNotPurge]);

        $advisor->supports($versionToBePurged)->shouldBeCalled()->willReturn(true);
        $advisor->isPurgeable($versionToBePurged, Argument::type('array'))
            ->shouldBeCalled()
            ->willReturn(true);

        $advisor->supports($versionNotSupported)->shouldBeCalled()->willReturn(false);
        $advisor->isPurgeable($versionNotSupported, Argument::type('array'))
            ->shouldNotBeCalled();

        $advisor->supports($versionToNotPurge)->shouldBeCalled()->willReturn(true);
        $advisor->isPurgeable($versionToNotPurge, Argument::type('array'))
            ->shouldBeCalled()
            ->willReturn(false);

        $this->addVersionPurgerAdvisor($advisor);

        $versionRemover->removeAll([$versionToBePurged, $versionNotSupported])->shouldBeCalled();
        $objectDetacher->detachAll([$versionToBePurged, $versionNotSupported])->shouldBeCalled();
        $objectDetacher->detach($versionToNotPurge)->shouldBeCalled();

        $this->purge([])->shouldReturn(2);
    }
}
