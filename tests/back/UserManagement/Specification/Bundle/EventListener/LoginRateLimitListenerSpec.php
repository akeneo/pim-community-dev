<?php

namespace Specification\Akeneo\UserManagement\Bundle\EventListener;

use Akeneo\UserManagement\Bundle\EventListener\LoginRateLimitListener;
use Akeneo\UserManagement\Bundle\Manager\UserManager;
use Akeneo\UserManagement\Bundle\Model\LockedAccountException;
use Akeneo\UserManagement\Component\Model\UserInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\PassportInterface;
use Symfony\Component\Security\Http\Event\CheckPassportEvent;
use Symfony\Component\Security\Http\Event\LoginFailureEvent;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

class LoginRateLimitListenerSpec extends ObjectBehavior
{
    private const ACCOUNT_LOCK_DURATION = 2;
    private const ALLOWED_FAILED_ATTEMPTS = 10;

    public function let(UserManager $userManager)
    {
        $this->beConstructedWith($userManager, self::ACCOUNT_LOCK_DURATION, self::ALLOWED_FAILED_ATTEMPTS, false);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(LoginRateLimitListener::class);
    }

    public function it_can_authenticate_when_counter_is_reset(
        UserManager $userManager,
        Passport $passport,
        UserBadge $badge,
        UserInterface $user,
        CheckPassportEvent $event,
    ): void {
        $this->initUser($user, 0, null);
        $this->initPassport($passport, $badge, $user);
        $event->getPassport()->willReturn($passport);

        $this->checkPassport($event);

        $userManager->updateUser()->shouldNotHaveBeenCalled();
    }

    public function it_can_authenticate_under_max_limit_counter(
        Passport $passport,
        UserBadge $badge,
        UserInterface $user,
        CheckPassportEvent $event,
    ): void {
        $this->initUser($user, self::ALLOWED_FAILED_ATTEMPTS - 1, $this->getAuthenticationFailureResetDateFromNow(1));
        $this->initPassport($passport, $badge, $user);
        $event->getPassport()->willReturn($passport);

        $this->checkPassport($event);
    }

    public function it_rejects_authentication_when_limit_is_reached(
        Passport $passport,
        UserBadge $badge,
        UserInterface $user,
        CheckPassportEvent $event,
    ): void {
        $this->initUser($user, self::ALLOWED_FAILED_ATTEMPTS, $this->getAuthenticationFailureResetDateFromNow(self::ACCOUNT_LOCK_DURATION - 1));
        $this->initPassport($passport, $badge, $user);
        $event->getPassport()->willReturn($passport);

        $this->shouldThrow(new LockedAccountException(self::ACCOUNT_LOCK_DURATION))
            ->duringCheckPassport($event);
    }

    public function it_rejects_authentication_when_user_just_reach_max_attempt(
        Passport $passport,
        UserBadge $badge,
        UserInterface $user,
        CheckPassportEvent $event,
    ): void {
        $this->initUser($user, self::ALLOWED_FAILED_ATTEMPTS, $this->getAuthenticationFailureResetDateFromNow(self::ACCOUNT_LOCK_DURATION - 1));
        $this->initPassport($passport, $badge, $user);
        $event->getPassport()->willReturn($passport);

        $this->shouldThrow(LockedAccountException::class)
            ->duringCheckPassport($event);
    }

    public function it_increase_failed_attempts_counter_on_login_failure(
        Passport $passport,
        UserBadge $badge,
        UserInterface $user,
        LoginFailureEvent $event,
        UserManager $userManager,
    ): void {
        $this->initUser($user,self::ALLOWED_FAILED_ATTEMPTS - 1, $this->getAuthenticationFailureResetDateFromNow(self::ACCOUNT_LOCK_DURATION - 1));
        $this->initPassport($passport, $badge, $user);
        $event->getPassport()->willReturn($passport);

        $this->onFailureLogin($event);

        $this->lockStateShouldBeUpdated($userManager, $user, self::ALLOWED_FAILED_ATTEMPTS);
    }

    public function it_consider_login_has_failed_when_passport_is_empty(
        UserInterface $user,
        LoginFailureEvent $event,
    ): void {
        $this->initUser($user,self::ALLOWED_FAILED_ATTEMPTS - 1, $this->getAuthenticationFailureResetDateFromNow(self::ACCOUNT_LOCK_DURATION - 1));
        $event->getPassport()->willReturn(null);

        $this->onFailureLogin($event)->shouldReturn(null);
    }

    public function it_consider_login_has_failed_when_passport_is_not_a_symfony_passport_instance(
        PassportInterface $passport,
        UserBadge $badge,
        UserInterface $user,
        LoginFailureEvent $event,
    ): void {
        $this->initUser($user,self::ALLOWED_FAILED_ATTEMPTS - 1, $this->getAuthenticationFailureResetDateFromNow(self::ACCOUNT_LOCK_DURATION - 1));
        $event->getPassport()->willReturn($passport);

        $this->onFailureLogin($event)->shouldReturn(null);
    }

    public function it_reset_failed_attempts_on_login_success(
        UserInterface $user,
        LoginSuccessEvent $event,
        UserManager $userManager,
    ): void {
        $this->initUser($user, 0, $this->getAuthenticationFailureResetDateFromNow(self::ACCOUNT_LOCK_DURATION + 1));
        $event->getUser()->willReturn($user);

        $this->onSuccessfulLogin($event);

        $this->lockStateShouldBeReset($user, $userManager);
    }

    private function initUser(UserInterface $user, int $consecutiveAuthenticationFailureCounter, \DateTime $authenticationFailureResetDate): void
    {
        $user->getConsecutiveAuthenticationFailureCounter()->willReturn($consecutiveAuthenticationFailureCounter);
        $user->getAuthenticationFailureResetDate()->willReturn($authenticationFailureResetDate);
        $user->getRoles()->willReturn([]);
        $user->getPassword()->willReturn('');
        $user->getSalt()->willReturn('');
    }

    private function initPassport(Passport $passport, UserBadge $badge, UserInterface $user): void
    {
        $passport->hasBadge(UserBadge::class)->willReturn(true);
        $passport->getBadge(UserBadge::class)->willReturn($badge);
        $badge->getUser()->willReturn($user);
    }

    private function getAuthenticationFailureResetDateFromNow(int $minutesBehind): \DateTime
    {
        return (new \DateTime())->modify("-{$minutesBehind} minute");
    }

    private function lockStateShouldBeUpdated(UserManager $userManager, UserInterface $user, int $expectedConsecutiveAuthenticationFailureCounter): void
    {
        $user->setConsecutiveAuthenticationFailureCounter($expectedConsecutiveAuthenticationFailureCounter)->shouldHaveBeenCalledOnce();
        $user->setAuthenticationFailureResetDate()->shouldNotBeCalled();
        $userManager->updateUser($user)->shouldHaveBeenCalled();
    }

    private function lockStateShouldBeReset(UserInterface $user, UserManager $userManager): void
    {
        $user->setConsecutiveAuthenticationFailureCounter(0)->shouldHaveBeenCalledOnce();
        $user->setAuthenticationFailureResetDate(null)->shouldHaveBeenCalled();
        $userManager->updateUser($user)->shouldHaveBeenCalled();
    }
}
