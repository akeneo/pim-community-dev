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
    const ACCOUNT_LOCK_DURATION = 120;
    const ALLOWED_FAILED_ATTEMPTS = 10;
    const PROVIDER_KEY = "providerKey";

    public function let(UserProviderInterface $userProvider, UserCheckerInterface $userChecker, EncoderFactoryInterface $encoderFactory, UserManager $userManager)
    {
        $this->beConstructedWith($userProvider, $userChecker, self::PROVIDER_KEY, $encoderFactory, $userManager, false, self::ACCOUNT_LOCK_DURATION, self::ALLOWED_FAILED_ATTEMPTS);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(CustomDaoAuthenticationProvider::class);
    }

    public function it_can_authenticate_when_counter_is_reset(UserManager $userManager)
    {
        $this->authenticate($this
            ->initTokenInterface(0, null));
        $userManager->updateUser()->shouldNotHaveBeenCalled();
    }

    public function it_can_authenticate_under_max_limit_counter(UserManager $userManager)
    {
        $usernamePasswordToken = $this->initTokenInterface(
            self::ALLOWED_FAILED_ATTEMPTS - 1,
            $this->initialFailedAttemptDateBackFromNow(1)
        );

        $this->authenticate($usernamePasswordToken);

        $this->assertCounterIsReset($usernamePasswordToken, $userManager);
    }

    public function it_rejects_authentication_when_limit_is_reached(UserManager $userManager)
    {
        //TODO check user manager not called.
        $usernamePasswordToken = $this->initTokenInterface(
            self::ALLOWED_FAILED_ATTEMPTS,
            self::initialFailedAttemptDateBackFromNow(self::ACCOUNT_LOCK_DURATION - 1)
        );
        $user = $usernamePasswordToken->getUser();

        $this->shouldThrow(new LockedAccountException(self::ACCOUNT_LOCK_DURATION))
            ->duringAuthenticate($usernamePasswordToken);

        $userManager->updateUser()->shouldNotHaveBeenCalled();
    }

    public function it_increase_failed_attempts_counter(UserManager $userManager, UserProviderInterface $userProvider, EncoderFactoryInterface $encoderFactory, PasswordEncoderInterface $passwordEncoder)
    {
        $user = $this->initUser(
            self::ALLOWED_FAILED_ATTEMPTS - 1,
            self::initialFailedAttemptDateBackFromNow(self::ACCOUNT_LOCK_DURATION - 1)
        );
        $usernamePasswordToken = $this->initializeMockForBadPassword("username", $encoderFactory, $passwordEncoder, $userProvider, $user);

        $this->shouldThrow(BadCredentialsException::class)
            ->duringAuthenticate($usernamePasswordToken);

        $this->checkLockStateUpdated($userManager, $user, self::ALLOWED_FAILED_ATTEMPTS);
    }

    public function it_initialize_failed_attempts_after_reset(UserManager $userManager, UserProviderInterface $userProvider, EncoderFactoryInterface $encoderFactory, PasswordEncoderInterface $passwordEncoder)
    {
        $user = $this->initUser(
            self::ALLOWED_FAILED_ATTEMPTS - 1,
            self::initialFailedAttemptDateBackFromNow(self::ACCOUNT_LOCK_DURATION - 1)
        );
        $usernamePasswordToken = $this->initializeMockForBadPassword("username", $encoderFactory, $passwordEncoder, $userProvider, $user);

        $this->shouldThrow(BadCredentialsException::class)
            ->duringAuthenticate($usernamePasswordToken);
    }

    protected function initTokenInterface(int $consecutiveAuthenticationFailures, \DateTime $dateInitialLoginFailure): UsernamePasswordToken
    {
        $user = $this->initUser($consecutiveAuthenticationFailures, $dateInitialLoginFailure);
        return new UsernamePasswordToken($user, null, self::PROVIDER_KEY);
    }

    private static function initialFailedAttemptDateBackFromNow(int $secondsBehind): \DateTime
    {
        return (new \DateTime())->modify("-{$secondsBehind} second");
    }

    protected function assertCounterIsReset(UsernamePasswordToken $usernamePasswordToken, UserManager $userManager): void
    {
        $user = $usernamePasswordToken->getUser();
        assert($user instanceof UserInterface);
        Assert::eq($user->getConsecutiveAuthenticationFailureCounter(), 0);
        Assert::null($user->getAuthenticationFailureResetDate());
        $userManager->updateUser(Argument::any())->shouldHaveBeenCalled();
    }

    protected function checkLockStateUpdated(UserManager $userManager, UserInterface $user, int $fal): void
    {
        Assert::eq($user->getConsecutiveAuthenticationFailureCounter(), $fal);
        $userManager->updateUser(Argument::any())->shouldHaveBeenCalled();
    }

    protected function initUser(int $consecutiveAuthenticationFailures, \DateTime $dateInitialLoginFailure): User
    {
        $user = new User();
        $user->setConsecutiveAuthenticationFailureCounter($consecutiveAuthenticationFailures);
        if ($dateInitialLoginFailure) {
            $user->setAuthenticationFailureResetDate($dateInitialLoginFailure);
        }
        return $user;
    }

    protected function initializeMockForBadPassword(string $username, EncoderFactoryInterface $encoderFactory, PasswordEncoderInterface $passwordEncoder, UserProviderInterface $userProvider, User $user): UsernamePasswordToken
    {
        $usernamePasswordToken = new UsernamePasswordToken($username, null, self::PROVIDER_KEY);
        $encoderFactory->getEncoder($username)->willReturn($passwordEncoder);
        $passwordEncoder->isPasswordValid(Argument::allOf())->willReturn(false);
        $userProvider->loadUserByUsername($username)->willReturn($user);
        return $usernamePasswordToken;
    }
}
