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
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlags;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\AuthenticationEvents;
use Symfony\Component\Security\Core\Event\AuthenticationSuccessEvent;

;

final class UserFirstConnectionSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private GetInvitedUserQuery $getInvitedUserQuery,
        private InvitedUserRepository $invitedUserRepository,
        private FeatureFlags $featureFlags,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            AuthenticationEvents::AUTHENTICATION_SUCCESS => 'onUserConnectionSuccess',
        ];
    }

    public function onUserConnectionSuccess(AuthenticationSuccessEvent $event)
    {
        if (!$this->featureFlags->isEnabled('free_trial')) {
            return;
        }

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
