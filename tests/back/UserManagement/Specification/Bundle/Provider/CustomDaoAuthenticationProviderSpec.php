<?php

namespace Specification\Akeneo\UserManagement\Bundle\Provider;

use Akeneo\UserManagement\Bundle\Manager\UserManager;
use Akeneo\UserManagement\Bundle\Model\LockedAccountException;
use Akeneo\UserManagement\Bundle\Provider\CustomDaoAuthenticationProvider;
use Akeneo\UserManagement\Component\Model\UserInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class CustomDaoAuthenticationProviderSpec extends ObjectBehavior
{
    const ACCOUNT_LOCK_DURATION = 2;
    const ALLOWED_FAILED_ATTEMPTS = 10;
    const PROVIDER_KEY = "providerKey";
    const USERNAME = "username";

    public function let(UserProviderInterface $userProvider, UserCheckerInterface $userChecker, PasswordHasherFactoryInterface $hasherFactory, UserManager $userManager)
    {
        $this->beConstructedWith($userProvider, $userChecker, self::PROVIDER_KEY, $hasherFactory, $userManager, self::ACCOUNT_LOCK_DURATION, self::ALLOWED_FAILED_ATTEMPTS, false);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(CustomDaoAuthenticationProvider::class);
    }

    public function it_can_authenticate_when_counter_is_reset(UserManager $userManager, UserInterface $user, UsernamePasswordToken $usernamePasswordToken)
    {
        $this->initUser($user, 0, null);
        $this->initUserNamePasswordTokenWithUser($usernamePasswordToken, $user);

        $this->authenticate($usernamePasswordToken);

        $userManager->updateUser()->shouldNotHaveBeenCalled();
    }

    public function it_can_authenticate_under_max_limit_counter(UserManager $userManager, UserInterface $user, UsernamePasswordToken $usernamePasswordToken)
    {
        $this->initUser(
            $user,
            self::ALLOWED_FAILED_ATTEMPTS - 1,
            $this->initialFailedAttemptDateBackFromNow(1)
        );

        $this->initUserNamePasswordTokenWithUser($usernamePasswordToken, $user);

        $this->authenticate($usernamePasswordToken);

        $this->shouldResetlockState($user, $userManager);
    }

    public function it_rejects_authentication_when_limit_is_reached(UserManager $userManager, UserInterface $user, UsernamePasswordToken $usernamePasswordToken)
    {
        $this->initUser($user, self::ALLOWED_FAILED_ATTEMPTS, $this->initialFailedAttemptDateBackFromNow(self::ACCOUNT_LOCK_DURATION - 1));
        $this->initUserNamePasswordTokenWithUser($usernamePasswordToken, $user);

        $this->shouldThrow(new LockedAccountException(self::ACCOUNT_LOCK_DURATION))
            ->duringAuthenticate($usernamePasswordToken);
        $this->shouldNotChangeState($userManager);
    }

    public function it_rejects_authentication_when_user_just_reach_max_attempt(
        UserInterface $user,
        UserProviderInterface $userProvider,
        UsernamePasswordToken $usernamePasswordToken,
        PasswordHasherInterface $passwordHasher,
        PasswordHasherFactoryInterface $hasherFactory
    ) {
        $this->initUser(
            $user,
            self::ALLOWED_FAILED_ATTEMPTS - 1,
            $this->initialFailedAttemptDateBackFromNow(self::ACCOUNT_LOCK_DURATION - 1)
        );

        $user->setConsecutiveAuthenticationFailureCounter(10)->shouldBeCalled()->will(function () {
            $this->getConsecutiveAuthenticationFailureCounter()->willReturn(20);
        });

        $this->initUsernamePasswordToken($usernamePasswordToken);
        $userProvider->loadUserByIdentifier(self::USERNAME)->shouldBeCalled()->willReturn($user);
        $passwordHasher->isPasswordValid(Argument::any(), Argument::any(), Argument::any())->willReturn(false);
        $hasherFactory->getPasswordHasher(Argument::any())->willReturn($passwordHasher);

        $this->shouldThrow(LockedAccountException::class)
            ->duringAuthenticate($usernamePasswordToken);
    }

    public function it_increase_failed_attempts_counter(
        PasswordHasherFactoryInterface $hasherFactory,
        UserManager $userManager,
        UserProviderInterface $userProvider,
        PasswordHasherInterface $passwordHasher,
        UserInterface $user,
        UsernamePasswordToken $usernamePasswordToken
    ) {
        $this->initUser(
            $user,
            self::ALLOWED_FAILED_ATTEMPTS - 1,
            $this->initialFailedAttemptDateBackFromNow(self::ACCOUNT_LOCK_DURATION - 1)
        );

        $this->initUsernamePasswordToken($usernamePasswordToken);
        $userProvider->loadUserByIdentifier(self::USERNAME)->shouldBeCalled()->willReturn($user);
        $passwordHasher->isPasswordValid(Argument::any(), Argument::any(), Argument::any())->willReturn(false);
        $hasherFactory->getPasswordHasher(Argument::any())->willReturn($passwordHasher);
        $this->shouldThrow(BadCredentialsException::class)
            ->duringAuthenticate($usernamePasswordToken);

        $this->checkLockStateUpdated($userManager, $user, self::ALLOWED_FAILED_ATTEMPTS);
    }

    public function it_initialize_failed_attempts_after_reset(UserManager $userManager, UserProviderInterface $userProvider, PasswordHasherFactoryInterface $hasherFactory, PasswordHasherInterface $passwordHasher, UserInterface $user, UsernamePasswordToken $usernamePasswordToken)
    {
        $this->initUser(
            $user,
            self::ALLOWED_FAILED_ATTEMPTS - 1,
            self::initialFailedAttemptDateBackFromNow(self::ACCOUNT_LOCK_DURATION + 1)
        );
        $user->getConsecutiveAuthenticationFailureCounter()->willReturn(0);
        $this->initUsernamePasswordToken($usernamePasswordToken);
        $userProvider->loadUserByIdentifier(self::USERNAME)->willReturn($user);
        $hasherFactory->getPasswordHasher(Argument::any())->willReturn($passwordHasher);

        $this->shouldThrow(BadCredentialsException::class)
            ->duringAuthenticate($usernamePasswordToken);

        $this->checkLockStateReset($user, $userManager);

        $this->checkLockStateInitialized($userManager, $user);
    }

    private static function initialFailedAttemptDateBackFromNow(int $secondsBehind): \DateTime
    {
        return (new \DateTime())->modify("-{$secondsBehind} minute");
    }

    private function checkLockStateUpdated(UserManager $userManager, UserInterface $user, int $fal): void
    {
        $user->setConsecutiveAuthenticationFailureCounter($fal)->shouldHaveBeenCalledOnce();
        $user->setAuthenticationFailureResetDate()->shouldNotBeCalled();
        $userManager->updateUser(Argument::any())->shouldHaveBeenCalled();
    }

    private function checkLockStateInitialized(UserManager $userManager, UserInterface $user)
    {
        $user->setConsecutiveAuthenticationFailureCounter(1)->shouldHaveBeenCalledOnce();
        $user->setAuthenticationFailureResetDate()->shouldNotBeCalled();
        $userManager->updateUser(Argument::any())->shouldHaveBeenCalled();
    }

    private function initUsernamePasswordToken(UsernamePasswordToken $usernamePasswordToken)
    {
        $usernamePasswordToken->getUser()->willReturn(self::USERNAME);
        $usernamePasswordToken->getFirewallName()->willReturn(self::PROVIDER_KEY);
        $usernamePasswordToken->getRoleNames(false)->willReturn([]);
        $usernamePasswordToken->getAttributes()->willReturn([]);
        $usernamePasswordToken->getUserIdentifier()->willReturn(self::USERNAME);
        $usernamePasswordToken->getCredentials()->willReturn('');
    }

    private function initUser(UserInterface $user, int $i, \DateTime $initialFailureDate): void
    {
        $user->getConsecutiveAuthenticationFailureCounter()->willReturn($i);
        $user->getAuthenticationFailureResetDate()->willReturn($initialFailureDate);
        $user->getRoles()->willReturn([]);
        $user->getPassword()->willReturn("");
        $user->getSalt()->willReturn("");
    }

    private function shouldResetlockState($user, $userManager): void
    {
        $user->setConsecutiveAuthenticationFailureCounter(0)->shouldHaveBeenCalledOnce();
        $user->setAuthenticationFailureResetDate(null)->shouldHaveBeenCalledOnce();
        $userManager->updateUser(Argument::any())->shouldHaveBeenCalled();
    }

    private function shouldNotChangeState($userManager): \Prophecy\Prophecy\MethodProphecy
    {
        return $userManager->updateUser()->shouldNotHaveBeenCalled();
    }

    private function initUserNamePasswordTokenWithUser(UsernamePasswordToken $usernamePasswordToken, UserInterface $user): void
    {
        $usernamePasswordToken->getUser()->willReturn($user);
        $usernamePasswordToken->getFirewallName()->willReturn(self::PROVIDER_KEY);
        $usernamePasswordToken->getRoleNames()->willReturn([]);
        $usernamePasswordToken->getAttributes()->willReturn([]);
        $usernamePasswordToken->getUserIdentifier()->willReturn(self::USERNAME);
        $usernamePasswordToken->getCredentials()->willReturn([]);
    }

    private function checkLockStateReset(UserInterface $user, UserManager $userManager): void
    {
        $user->setConsecutiveAuthenticationFailureCounter(0)->shouldHaveBeenCalledOnce();
        $user->setAuthenticationFailureResetDate(null)->shouldHaveBeenCalled();
        $userManager->updateUser($user)->shouldHaveBeenCalled();
    }
}
