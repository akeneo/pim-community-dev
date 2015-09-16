<?php

namespace spec\Pim\Bundle\VersioningBundle\EventSubscriber;

use Akeneo\Bundle\StorageUtilsBundle\Event\BaseEvents;
use Akeneo\Component\StorageUtils\Event\RemoveEvent;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\VersioningBundle\Factory\VersionFactory;
use Pim\Bundle\VersioningBundle\Model\Version;
use Pim\Bundle\VersioningBundle\Model\VersionableInterface;
use Pim\Bundle\VersioningBundle\Repository\VersionRepositoryInterface;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class AddRemoveVersionSubscriberSpec extends ObjectBehavior
{
    function let(
        VersionFactory $versionFactory,
        VersionRepositoryInterface $versionRepository,
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorizationChecker,
        SaverInterface $versionSaver
    ) {
        $this->beConstructedWith($versionFactory, $versionRepository, $tokenStorage, $authorizationChecker, $versionSaver);
    }

    function it_subscribes_to_post_remove_events()
    {
        $this->getSubscribedEvents()->shouldReturn([
            BaseEvents::POST_REMOVE => 'postRemove',
        ]);
    }

    function it_creates_a_version_on_object_deletion($versionFactory, $versionRepository, $tokenStorage, $authorizationChecker, $versionSaver, Version $previousVersion, Version $removeVersion, TokenInterface $token, UserInterface $admin, VersionableInterface $price, RemoveEvent $event)
    {
        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($admin);
        $admin->getUsername()->willReturn('admin');
        $authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED')->willReturn(true);

        $versionRepository->getNewestLogEntry(Argument::any(), 12)->willReturn($previousVersion);
        $previousVersion->getVersion()->willReturn(11);
        $previousVersion->getSnapshot()->willReturn(['foo' => 'bar']);

        $versionFactory->create(Argument::Any(), 12, 'admin', 'Deleted')->willReturn($removeVersion);
        $removeVersion->setVersion(12)->willReturn($removeVersion);
        $removeVersion->setSnapshot(['foo' => 'bar'])->willReturn($removeVersion);
        $removeVersion->setChangeset([])->willReturn($removeVersion);

        $versionSaver->save($removeVersion)->shouldBeCalled();

        $event->getSubject()->willReturn($price);
        $event->getSubjectId()->willReturn(12);

        $this->postRemove($event);
    }
}
