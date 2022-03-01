<?php


namespace Akeneo\UserManagement\Bundle\Provider;

use Akeneo\UserManagement\Bundle\Manager\UserManager;
use Akeneo\UserManagement\Bundle\Model\LockedAccountException;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Symfony\Component\Security\Core\Authentication\Provider\DaoAuthenticationProvider;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface as SecurityUserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CustomDaoAuthenticationProvider extends DaoAuthenticationProvider
{
    private UserManager $userManager;

    private int $accountLockDuration;

    private int $accountMaxConsecutiveFailure;

    public function __construct(UserProviderInterface $userProvider, UserCheckerInterface $userChecker, string $providerKey, EncoderFactoryInterface $encoderFactory, UserManager $userManager, int $accountLockDuration, int $accountMaxConsecutiveFailure, bool $hideUserNotFoundExceptions = true)
    {
        parent::__construct($userProvider, $userChecker, $providerKey, $encoderFactory, $hideUserNotFoundExceptions);
        $this->userManager = $userManager;
        $this->accountLockDuration = $accountLockDuration;
        $this->accountMaxConsecutiveFailure = $accountMaxConsecutiveFailure;
    }

    /**
     * {@inheritdoc}
     */
    public function checkAuthentication(SecurityUserInterface $user, UsernamePasswordToken $token)
    {
        $this->assertAccountIsUnlocked($user);
        if ($this->shouldResetCounter($user)) {
            $this->resetLockingState($user);
        }
        try {
            parent::checkAuthentication($user, $token);
            $this->resetLockingState($user);
        } catch (BadCredentialsException $e) {
            $this->incrementFailureCounter($user);
            $this->assertAccountIsUnlocked($user);

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

    private function assertAccountIsUnlocked(UserInterface $user): void
    {
        if ($this->isCounterReset($user)) {
            return;
        }
        if ($this->isWithinLockTimePeriod($user)
            && ($user->getConsecutiveAuthenticationFailureCounter() >= $this->accountMaxConsecutiveFailure)) {
            throw new LockedAccountException($this->accountLockDuration);
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
        return ((new \DateTime())->modify("-{$this->accountLockDuration} minute") <= $user->getAuthenticationFailureResetDate());
    }

    private function isCounterReset(UserInterface $user): bool
    {
        return null === $user->getAuthenticationFailureResetDate();
    }

    protected function shouldResetCounter(SecurityUserInterface $user): bool
    {
        return !$this->isCounterReset($user)
            && !$this->isWithinLockTimePeriod($user);
    }
}
