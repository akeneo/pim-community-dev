<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\FreeTrial\Infrastructure\Subscriber;

use Akeneo\FreeTrial\Domain\Query\GetInvitedUserQuery;
use Akeneo\FreeTrial\Domain\Repository\InvitedUserRepository;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\AuthenticationEvents;
use Symfony\Component\Security\Core\Event\AuthenticationSuccessEvent;

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
