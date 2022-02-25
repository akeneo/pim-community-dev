<?php

namespace Specification\Akeneo\UserManagement\Bundle\Provider;

use Akeneo\UserManagement\Bundle\Manager\UserManager;
use Akeneo\UserManagement\Bundle\Model\LockedAccountException;
use Akeneo\UserManagement\Bundle\Provider\CustomDaoAuthenticationProvider;
use Akeneo\UserManagement\Component\Model\User;
use Doctrine\DBAL\Connection;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
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

    public function let(UserProviderInterface $userProvider, UserCheckerInterface $userChecker, EncoderFactoryInterface $encoderFactory, UserManager $userManager, Connection $connection)
    {
        $this->beConstructedWith($userProvider, $userChecker, self::PROVIDER_KEY, $encoderFactory, $userManager, $connection, self::ACCOUNT_LOCK_DURATION, self::ALLOWED_FAILED_ATTEMPTS, false);

    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(CustomDaoAuthenticationProvider::class);
    }

    public function it_can_authenticate_when_counter_is_reset(User $user, UsernamePasswordToken $usernamePasswordToken, Connection $connection)
    {
        $this->initUser($user, 0, null);
        $this->initUserNamePasswordTokenWithUser($usernamePasswordToken, $user);

        $this->authenticate($usernamePasswordToken);

        $connection->executeQuery()->shouldNotHaveBeenCalled();
    }

    public function it_can_authenticate_under_max_limit_counter(Connection $connection, User $user, UsernamePasswordToken $usernamePasswordToken)
    {
        $this->initUser(
            $user,
            self::ALLOWED_FAILED_ATTEMPTS - 1,
            $this->initialFailedAttemptDateBackFromNow(1)
        );

        $this->initUserNamePasswordTokenWithUser($usernamePasswordToken, $user);

        $this->authenticate($usernamePasswordToken);

        $this->shouldResetlockState($user, $connection);
    }

    public function it_rejects_authentication_when_limit_is_reached(Connection $connection, User $user, UsernamePasswordToken $usernamePasswordToken)
    {
        $this->initUser($user, self::ALLOWED_FAILED_ATTEMPTS, $this->initialFailedAttemptDateBackFromNow(self::ACCOUNT_LOCK_DURATION - 1));
        $this->initUserNamePasswordTokenWithUser($usernamePasswordToken, $user);

        $this->shouldThrow(new LockedAccountException(self::ACCOUNT_LOCK_DURATION))
            ->duringAuthenticate($usernamePasswordToken);
        $this->shouldNotChangeState($connection);
    }

    public function it_rejects_authentication_when_user_just_reach_max_attempt(
        User $user,
        UserProviderInterface $userProvider,
        UsernamePasswordToken $usernamePasswordToken,
        PasswordEncoderInterface $passwordEncoder,
        EncoderFactoryInterface $encoderFactory
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
        $userProvider->loadUserByUsername(self::USERNAME)->shouldBeCalled()->willReturn($user);
        $passwordEncoder->isPasswordValid(Argument::any(), Argument::any(), Argument::any())->willReturn(false);
        $encoderFactory->getEncoder(Argument::any())->willReturn($passwordEncoder);

        $this->shouldThrow(LockedAccountException::class)
            ->duringAuthenticate($usernamePasswordToken);
    }

    public function it_increase_failed_attempts_counter(
        EncoderFactoryInterface $encoderFactory,
        Connection $connection,
        UserProviderInterface $userProvider,
        PasswordEncoderInterface $passwordEncoder,
        User $user,
        UsernamePasswordToken $usernamePasswordToken
    ) {
        $this->initUser(
            $user,
            self::ALLOWED_FAILED_ATTEMPTS - 1,
            $this->initialFailedAttemptDateBackFromNow(self::ACCOUNT_LOCK_DURATION - 1)
        );

        $this->initUsernamePasswordToken($usernamePasswordToken);
        $userProvider->loadUserByUsername(self::USERNAME)->shouldBeCalled()->willReturn($user);
        $passwordEncoder->isPasswordValid(Argument::any(), Argument::any(), Argument::any())->willReturn(false);
        $encoderFactory->getEncoder(Argument::any())->willReturn($passwordEncoder);
        $this->shouldThrow(BadCredentialsException::class)
            ->duringAuthenticate($usernamePasswordToken);

        $this->checkLockStateUpdated($connection, $user, self::ALLOWED_FAILED_ATTEMPTS);
    }

    public function it_initialize_failed_attempts_after_reset(Connection $connection, UserProviderInterface $userProvider, EncoderFactoryInterface $encoderFactory, PasswordEncoderInterface $passwordEncoder, User $user, UsernamePasswordToken $usernamePasswordToken)
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

        $this->checkLockStateReset($user, $connection);

        $this->checkLockStateInitialized($connection, $user);
    }

    private static function initialFailedAttemptDateBackFromNow(int $secondsBehind): \DateTime
    {
        return (new \DateTime())->modify("-{$secondsBehind} minute");
    }

    private function checkLockStateUpdated(Connection $connection, User $user, int $fal): void
    {
        $user->setConsecutiveAuthenticationFailureCounter($fal)->shouldHaveBeenCalledOnce();
        $user->setAuthenticationFailureResetDate()->shouldNotBeCalled();
        $connection->executeQuery(Argument::any(), Argument::any(), Argument::any())->shouldHaveBeenCalled();
    }

    private function checkLockStateInitialized(Connection $connection, User $user)
    {
        $user->setConsecutiveAuthenticationFailureCounter(1)->shouldHaveBeenCalledOnce();
        $user->setAuthenticationFailureResetDate()->shouldNotBeCalled();
        $connection->executeQuery(Argument::any(), Argument::any(), Argument::any())->shouldHaveBeenCalled();
    }

    private function initUsernamePasswordToken(UsernamePasswordToken $usernamePasswordToken)
    {
        $usernamePasswordToken->getUser()->willReturn(self::USERNAME);
        $usernamePasswordToken->getRoleNames(false)->willReturn([]);
        $usernamePasswordToken->getAttributes()->willReturn([]);
        $usernamePasswordToken->getCredentials()->willReturn('');
        $usernamePasswordToken->getProviderKey()->willReturn(self::PROVIDER_KEY);
        $usernamePasswordToken->getUsername()->willReturn(self::USERNAME);
        $usernamePasswordToken->getRoles(false)->willReturn([]);
    }

    private function initUser(User $user, int $i, \DateTime $initialFailureDate): void
    {
        $user->getConsecutiveAuthenticationFailureCounter()->willReturn($i);
        $user->getAuthenticationFailureResetDate()->willReturn($initialFailureDate);
        $user->getRoles()->willReturn([]);
        $user->getPassword()->willReturn("");
        $user->getSalt()->willReturn("");
        $user->getId()->willReturn(1);
    }

    private function shouldResetlockState($user, Connection $connection): void
    {
        $user->setConsecutiveAuthenticationFailureCounter(0)->shouldHaveBeenCalledOnce();
        $user->setAuthenticationFailureResetDate(null)->shouldHaveBeenCalledOnce();
        $connection->executeQuery(Argument::any(), Argument::any(), Argument::any())->shouldHaveBeenCalled();
    }

    private function shouldNotChangeState(Connection $connection): \Prophecy\Prophecy\MethodProphecy
    {
        return $connection->executeQuery()->shouldNotHaveBeenCalled();
    }

    private function initUserNamePasswordTokenWithUser(UsernamePasswordToken $usernamePasswordToken, User $user): void
    {
        $usernamePasswordToken->getUser()->willReturn($user);
        $usernamePasswordToken->getRoleNames()->willReturn([]);
        $usernamePasswordToken->getAttributes()->willReturn([]);
        $usernamePasswordToken->getCredentials()->willReturn([]);
        $usernamePasswordToken->getProviderKey()->willReturn(self::PROVIDER_KEY);
        $usernamePasswordToken->getUsername()->willReturn(self::USERNAME);
        $usernamePasswordToken->getRoles(false)->willReturn([]);
    }

    private function checkLockStateReset(User $user, Connection $connection): void
    {
        $user->setConsecutiveAuthenticationFailureCounter(0)->shouldHaveBeenCalledOnce();
        $user->setAuthenticationFailureResetDate(null)->shouldHaveBeenCalled();
        $connection->executeQuery(Argument::any(), Argument::any(), Argument::any())->shouldHaveBeenCalled();
    }
}
