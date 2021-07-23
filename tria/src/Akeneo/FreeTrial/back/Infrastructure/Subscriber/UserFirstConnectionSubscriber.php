<?php

declare(strict_types=1);

namespace Akeneo\FreeTrial\Infrastructure\Subscriber;

use Akeneo\FreeTrial\Domain\Query\GetInvitedUserQuery;
use Akeneo\FreeTrial\Domain\Repository\InvitedUserRepository;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\AuthenticationEvents;
use Symfony\Component\Security\Core\Event\AuthenticationSuccessEvent;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class UserFirstConnectionSubscriber implements EventSubscriberInterface
{
    private GetInvitedUserQuery $getInvitedUserQuery;

    private InvitedUserRepository $invitedUserRepository;

    public function __construct(
        GetInvitedUserQuery $getInvitedUserQuery,
        InvitedUserRepository $invitedUserRepository
    ) {
        $this->getInvitedUserQuery = $getInvitedUserQuery;
        $this->invitedUserRepository = $invitedUserRepository;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            AuthenticationEvents::AUTHENTICATION_SUCCESS => 'onUserConnectionSuccess',
        ];
    }

    public function onUserConnectionSuccess(AuthenticationSuccessEvent $event)
    {
        $user = $event->getAuthenticationToken()->getUser();
        if (!$user instanceof UserInterface) {
            return;
        }

        $invitedUser = $this->getInvitedUserQuery->execute($user->getEmail());
        if (!$invitedUser) {
            return;
        }

        $invitedUser->activate();
        $this->invitedUserRepository->save($invitedUser);
    }
}
