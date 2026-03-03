<?php

namespace Akeneo\Tool\Bundle\ConnectorBundle\EventListener;

use Akeneo\Tool\Component\Batch\Event\EventInterface;
use Akeneo\Tool\Component\Batch\Event\JobExecutionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * Authenticate a job execution with the user that launched the job.
 *
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobExecutionAuthenticator implements EventSubscriberInterface
{
    /**
     * @param UserProviderInterface $jobUserProvider
     * @param UserProviderInterface $uiUserProvider
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(
        protected UserProviderInterface $jobUserProvider,
        protected UserProviderInterface $uiUserProvider,
        protected TokenStorageInterface $tokenStorage
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            EventInterface::BEFORE_JOB_EXECUTION => 'authenticate'
        ];
    }

    /**
     * Authenticate or not the job execution with the user that launched the job,
     * according to the parameters of the job execution.
     *
     * @throws UserNotFoundException
     *
     * @param JobExecutionEvent $event
     */
    public function authenticate(JobExecutionEvent $event): void
    {
        $jobExecution = $event->getJobExecution();
        $jobParameters = $jobExecution->getJobParameters();
        $username = $jobExecution->getUser();

        if (null === $username || null === $jobParameters || !$jobParameters->has('is_user_authenticated')) {
            return;
        }

        if (false === $jobParameters->get('is_user_authenticated')) {
            return;
        }

        try {
            $user = $this->jobUserProvider->loadUserByIdentifier($username);
        } catch (UserNotFoundException) {
            // Fallback to UI user for retro-compatibility
            $user = $this->uiUserProvider->loadUserByIdentifier($username);
        }

        $token = new UsernamePasswordToken($user, 'main', $user->getRoles());
        $this->tokenStorage->setToken($token);
    }
}
