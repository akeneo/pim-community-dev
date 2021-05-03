<?php


namespace Akeneo\UserManagement\Bundle\Provider;

use Akeneo\UserManagement\Bundle\Manager\UserManager;
use Akeneo\UserManagement\Bundle\Model\LockedAccountException;
use Akeneo\UserManagement\Component\Model\User;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Symfony\Component\Security\Core\Authentication\Provider\DaoAuthenticationProvider;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface as SecurityUserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class CustomDaoAuthenticationProvider extends DaoAuthenticationProvider
{
    /** @var UserManager */
    private $userManager;

    /** @var int */
    private $accountLockDuration;

    /** @var int */
    private $accountMaxConsecutiveFailure;

    public function __construct(UserProviderInterface $userProvider, UserCheckerInterface $userChecker, string $providerKey, EncoderFactoryInterface $encoderFactory, UserManager $userManager, bool $hideUserNotFoundExceptions = true, int $accountLockDuration, int $accountMaxConsecutiveFailure)
    {
        parent::__construct($userProvider, $userChecker, $providerKey, $encoderFactory, $hideUserNotFoundExceptions);
        $this->userManager = $userManager;
        $this->accountLockDuration = $accountLockDuration;
        $this->accountMaxConsecutiveFailure = $accountMaxConsecutiveFailure;
    }

    /**
     * {@inheritdoc}
     */
    public function checkAuthentication(\Symfony\Component\Security\Core\User\UserInterface $user, UsernamePasswordToken $token)
    {
        assert($user instanceof UserInterface);
        $this->validateAccountUnlocked($user);
        try {
            parent::checkAuthentication($user, $token);
            $this->resetLockingState($user);
        } catch (BadCredentialsException $e) {
            $this->incrementFailureCounter($user);
            throw $e;
        }
    }

    private function incrementFailureCounter(UserInterface $user)
    {
        if (null === $user->getAuthenticationFailureResetDate()) {
            $user->setAuthenticationFailureResetDate(new \DateTime());
        }
        $user->setConsecutiveAuthenticationFailureCounter(
            1 + $user->getConsecutiveAuthenticationFailureCounter()
        );
        $this->userManager->updateUser($user);
    }

    private function validateAccountUnlocked(UserInterface $user): void
    {
        if ($this->isCounterReset($user)) {
            return;
        }
        if ($this->isWithinLockTimePeriod($user)
            && ($user->getConsecutiveAuthenticationFailureCounter() >= $this->accountMaxConsecutiveFailure)) {
            throw new LockedAccountException($this->accountLockDuration);
        }

        if (!$this->isWithinLockTimePeriod($user)) {
            $this->resetLockingState($user);
        }
    }

    private function resetLockingState(UserInterface $user): void
    {
        if ($this->isCounterReset($user)) {
            return;
        }

        $user->setAuthenticationFailureResetDate(null);
        $user->setConsecutiveAuthenticationFailureCounter(0);
        $this->userManager->updateUser($user);
    }

    private function isWithinLockTimePeriod(UserInterface $user): bool
    {
        return ((new \DateTime())->modify("-{$this->accountLockDuration} second") <= $user->getAuthenticationFailureResetDate());
    }

    private function isCounterReset(UserInterface $user): bool
    {
        return null === $user->getAuthenticationFailureResetDate();
    }
}
