<?php

namespace Specification\Akeneo\UserManagement\Bundle\Provider;

use Akeneo\UserManagement\Bundle\Manager\UserManager;
use Akeneo\UserManagement\Bundle\Model\LockedAccountException;
use Akeneo\UserManagement\Bundle\Provider\CustomDaoAuthenticationProvider;
use Akeneo\UserManagement\Component\Model\User;
use Akeneo\UserManagement\Component\Model\UserInterface;
use PhpSpec\Matcher\Matcher;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Webmozart\Assert\Assert;

class CustomDaoAuthenticationProviderSpec extends ObjectBehavior
{
    const ACCOUNT_LOCK_DURATION = 2;
    const ALLOWED_FAILED_ATTEMPTS = 10;
    const PROVIDER_KEY = "providerKey";
    const USERNAME = "username";

    public function let(UserProviderInterface $userProvider, UserCheckerInterface $userChecker, EncoderFactoryInterface $encoderFactory, UserManager $userManager)
    {
        $this->beConstructedWith($userProvider, $userChecker, self::PROVIDER_KEY, $encoderFactory, $userManager, self::ACCOUNT_LOCK_DURATION, self::ALLOWED_FAILED_ATTEMPTS, false);

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

    public function it_increase_failed_attempts_counter(EncoderFactoryInterface $encoderFactory, UserManager $userManager, UserProviderInterface $userProvider, PasswordEncoderInterface $passwordEncoder, UserInterface $user, UsernamePasswordToken $usernamePasswordToken)
    {
        $this->initUser(
            $user,
            self::ALLOWED_FAILED_ATTEMPTS - 1,
            $this->initialFailedAttemptDateBackFromNow(self::ACCOUNT_LOCK_DURATION - 1)
        );

        $this->initUsernamePasswordToken($usernamePasswordToken);
        $userProvider->loadUserByUsername(self::USERNAME)->willReturn($user);
        $passwordEncoder->isPasswordValid(Argument::any(), Argument::any(), Argument::any())->willReturn(false);
        $encoderFactory->getEncoder(Argument::any())->willReturn($passwordEncoder);

        $this->shouldThrow(BadCredentialsException::class)
            ->duringAuthenticate($usernamePasswordToken);

        $this->checkLockStateUpdated($userManager, $user, self::ALLOWED_FAILED_ATTEMPTS);
    }

    public function it_initialize_failed_attempts_after_reset(UserManager $userManager, UserProviderInterface $userProvider, EncoderFactoryInterface $encoderFactory, PasswordEncoderInterface $passwordEncoder, UserInterface $user, UsernamePasswordToken $usernamePasswordToken)
    {
        $this->initUser(
            $user,
            self::ALLOWED_FAILED_ATTEMPTS - 1,
            self::initialFailedAttemptDateBackFromNow(self::ACCOUNT_LOCK_DURATION + 1)
        );
        $user->getConsecutiveAuthenticationFailureCounter()->willReturn(0);
        $this->initUsernamePasswordToken($usernamePasswordToken);
        $userProvider->loadUserByUsername(self::USERNAME)->willReturn($user);
        $encoderFactory->getEncoder(Argument::any())->willReturn($passwordEncoder);

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
        $usernamePasswordToken->getProviderKey()->willReturn(self::PROVIDER_KEY);
        $usernamePasswordToken->getRoles(false)->willReturn([]);
        $usernamePasswordToken->getAttributes()->willReturn([]);
        $usernamePasswordToken->getUsername()->willReturn(self::USERNAME);
        $usernamePasswordToken->getCredentials()->willReturn([]);
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
        $usernamePasswordToken->getProviderKey()->willReturn(self::PROVIDER_KEY);
        $usernamePasswordToken->getRoles(false)->willReturn([]);
        $usernamePasswordToken->getAttributes()->willReturn([]);
        $usernamePasswordToken->getUsername()->willReturn(self::USERNAME);
        $usernamePasswordToken->getCredentials()->willReturn([]);
    }

    private function checkLockStateReset(UserInterface $user, UserManager $userManager): void
    {
        $user->setConsecutiveAuthenticationFailureCounter(0)->shouldHaveBeenCalledOnce();
        $user->setAuthenticationFailureResetDate(null)->shouldHaveBeenCalled();
        $userManager->updateUser($user)->shouldHaveBeenCalled();
    }

}
