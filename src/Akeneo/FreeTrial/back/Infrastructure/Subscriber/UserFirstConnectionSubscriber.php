<?php

declare(strict_types=1);

namespace Akeneo\FreeTrial\Infrastructure\Subscriber;

use Akeneo\FreeTrial\Domain\Query\GetInvitedUserQuery;
use Akeneo\FreeTrial\Domain\Repository\InvitedUserRepository;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Akeneo\UserManagement\Component\Model\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\AuthenticationEvents;
use Symfony\Component\Security\Core\Event\AuthenticationSuccessEvent;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class UserFirstConnectionSubscriber implements EventSubscriberInterface
{
    private FeatureFlag $featureFlag;

    private GetInvitedUserQuery $getInvitedUserQuery;

    private InvitedUserRepository $invitedUserRepository;

    public function __construct(
        FeatureFlag $featureFlag,
        GetInvitedUserQuery $getInvitedUserQuery,
        InvitedUserRepository $invitedUserRepository
    ) {

        $this->featureFlag = $featureFlag;
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
        if (!$this->featureFlag->isEnabled()) {
            return;
        }

        $user = $event->getAuthenticationToken()->getUser();
        if (!$user instanceof User) {
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
