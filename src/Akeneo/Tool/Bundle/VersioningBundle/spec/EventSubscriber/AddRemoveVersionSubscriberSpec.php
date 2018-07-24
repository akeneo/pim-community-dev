<?php

namespace spec\Akeneo\Tool\Bundle\VersioningBundle\EventSubscriber;

use Akeneo\Tool\Component\StorageUtils\Event\RemoveEvent;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Akeneo\Tool\Component\Versioning\Model\VersionableInterface;
use Akeneo\Tool\Component\Versioning\Model\VersionInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Tool\Bundle\VersioningBundle\Factory\VersionFactory;
use Akeneo\Tool\Bundle\VersioningBundle\Repository\VersionRepositoryInterface;
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
        $this->beConstructedWith(
            $versionFactory,
            $versionRepository,
            $tokenStorage,
            $authorizationChecker,
            $versionSaver
        );
    }

    function it_subscribes_to_post_remove_events()
    {
        $this->getSubscribedEvents()->shouldReturn([
            StorageEvents::PRE_REMOVE => 'addRemoveVersion',
        ]);
    }

    function it_creates_a_version_on_versionable_object_deletion(
        $versionFactory,
        $versionRepository,
        $tokenStorage,
        $authorizationChecker,
        $versionSaver,
        VersionInterface $previousVersion,
        VersionInterface $removeVersion,
        TokenInterface $token,
        UserInterface $admin,
        VersionableInterface $price,
        RemoveEvent $event
    ) {
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

        $saveOptions = ['flush' => true];

        $versionSaver->save($removeVersion, $saveOptions)->shouldBeCalled();

        $event->getSubject()->willReturn($price);
        $event->getSubjectId()->willReturn(12);
        $event->getArguments()->willReturn($saveOptions);

        $this->addRemoveVersion($event);
    }

    function it_does_not_create_a_version_on_not_versionable_object_deletion(
        $tokenStorage,
        $authorizationChecker,
        $versionSaver,
        VersionInterface $removeVersion,
        TokenInterface $token,
        UserInterface $admin,
        $notVersionableObject,
        RemoveEvent $event
    ) {
        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($admin);
        $admin->getUsername()->willReturn('admin');
        $authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED')->willReturn(true);

        $versionSaver->save($removeVersion, Argument::any())->shouldNotBeCalled();

        $event->getSubject()->willReturn($notVersionableObject);
        $this->addRemoveVersion($event);
    }
}
