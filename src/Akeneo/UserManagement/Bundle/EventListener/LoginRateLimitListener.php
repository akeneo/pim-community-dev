<?php

declare(strict_types=1);

namespace Akeneo\UserManagement\Bundle\EventListener;

use Akeneo\UserManagement\Bundle\Manager\UserManager;
use Akeneo\UserManagement\Bundle\Model\LockedAccountException;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Event\CheckPassportEvent;
use Symfony\Component\Security\Http\Event\LoginFailureEvent;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class LoginRateLimitListener implements EventSubscriberInterface
{
    public function __construct(
        private UserManager $userManager,
        private int $accountLockDuration,
        private int $accountMaxConsecutiveFailure,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CheckPassportEvent::class => ['checkPassport', 2080],
            LoginSuccessEvent::class => 'onSuccessfulLogin',
            LoginFailureEvent::class => 'onFailureLogin',
        ];
    }

    public function checkPassport(CheckPassportEvent $event): void
    {
        $user = $this->getUserFromPassport($event->getPassport());
        if (!$user instanceof UserInterface) {
            return;
        }

        if ($this->rateLimitIsExceeded($user)) {
            throw new LockedAccountException($this->accountLockDuration);
        }
    }

    public function onSuccessfulLogin(LoginSuccessEvent $event): void
    {
        $user = $event->getUser();
        if (!$user instanceof UserInterface) {
            return;
        }

        $user->setAuthenticationFailureResetDate(null);
        $user->setConsecutiveAuthenticationFailureCounter(0);
        $this->userManager->updateUser($user);
    }

    public function onFailureLogin(LoginFailureEvent $event)
    {
        $user = $this->getUserFromPassport($event->getPassport());
        if (!$user instanceof UserInterface) {
            return;
        }

        $this->incrementFailureCounter($user);
    }

    private function incrementFailureCounter(UserInterface $user)
    {
        if (null === $user->getAuthenticationFailureResetDate()) {
            $user->setAuthenticationFailureResetDate(new \DateTime());
        }

        $user->setConsecutiveAuthenticationFailureCounter(1 + $user->getConsecutiveAuthenticationFailureCounter());
        $this->userManager->updateUser($user);
    }

    private function getUserFromPassport($passport): ?UserInterface
    {
        if (!$passport instanceof Passport || !$passport->hasBadge(UserBadge::class)) {
            return null;
        }

        /** @var UserBadge $userBadge */
        $userBadge = $passport->getBadge(UserBadge::class);
        $user = $userBadge->getUser();
        if (!$user instanceof UserInterface) {
            return null;
        }

        return $user;
    }

    private function rateLimitIsExceeded(UserInterface $user): bool
    {
        return
            $this->isWithinLockTimePeriod($user)
            && ($user->getConsecutiveAuthenticationFailureCounter() >= $this->accountMaxConsecutiveFailure);
    }

    private function isWithinLockTimePeriod(UserInterface $user): bool
    {
        return ((new \DateTime())->modify("-{$this->accountLockDuration} minute") <= $user->getAuthenticationFailureResetDate());
    }
}
