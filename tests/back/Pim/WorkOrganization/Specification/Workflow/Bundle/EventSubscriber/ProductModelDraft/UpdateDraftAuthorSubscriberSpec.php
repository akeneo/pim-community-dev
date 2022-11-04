<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Bundle\EventSubscriber\ProductModelDraft;

use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Storage\Sql\ProductModelDraft\UpdateDraftAuthor;
use Akeneo\UserManagement\Component\Event\UserEvent;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Security\Core\User\UserInterface;

class UpdateDraftAuthorSubscriberSpec extends ObjectBehavior
{
    function let(UpdateDraftAuthor $updateProductModelDraftAuthor)
    {
        $this->beConstructedWith($updateProductModelDraftAuthor);
    }

    function it_subscribes_to_an_updated_user()
    {
        $this->getSubscribedEvents()->shouldReturn([
            UserEvent::POST_UPDATE => 'updateDraftAuthor',
        ]);
    }

    function it_does_not_update_author_if_the_user_is_not_set($updateProductModelDraftAuthor, GenericEvent $event)
    {
        $event->getSubject()->willReturn(new \stdClass());
        $updateProductModelDraftAuthor->execute(Argument::cetera())->shouldNotBeCalled();

        $this->updateDraftAuthor($event);
    }

    function it_does_not_update_author_if_username_is_not_updated(
        $updateProductModelDraftAuthor,
        GenericEvent $event,
        UserInterface $user
    ) {
        $event->getSubject()->willReturn($user);
        $event->getArgument('previous_username')->willReturn('foo');
        $user->getUserIdentifier()->willReturn('foo');

        $updateProductModelDraftAuthor->execute(Argument::cetera())->shouldNotBeCalled();

        $this->updateDraftAuthor($event);
    }

    function it_updates_author_if_username_is_updated(
        $updateProductModelDraftAuthor,
        GenericEvent $event,
        UserInterface $user
    ) {
        $event->getSubject()->willReturn($user);
        $event->getArgument('previous_username')->willReturn('foo');
        $user->getUserIdentifier()->willReturn('baz');

        $updateProductModelDraftAuthor->execute('foo', 'baz')->shouldBeCalled();

        $this->updateDraftAuthor($event);
    }
}
