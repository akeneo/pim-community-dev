<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Connector\JobLauncher;

use Akeneo\Pim\Automation\FranklinInsights\Application\ProductSubscription\Service\ScheduleFetchProductsInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Proposal\ValueObject\ProposalAuthor;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Connector\JobInstanceNames;
use Akeneo\Tool\Bundle\BatchBundle\Job\JobInstanceRepository;
use Akeneo\Tool\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\Tool\Bundle\BatchQueueBundle\Queue\JobExecutionMessageRepository;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class ScheduleFetchProductsJob implements ScheduleFetchProductsInterface
{
    /** @var JobLauncherInterface */
    private $queueJobLauncher;

    /** @var JobInstanceRepository */
    private $jobInstanceRepository;

    /** @var UserRepositoryInterface */
    private $userRepository;

    /** @var JobExecutionMessageRepository */
    private $jobExecutionMessageRepository;

    public function __construct(
        JobLauncherInterface $queueJobLauncher,
        JobInstanceRepository $jobInstanceRepository,
        UserRepositoryInterface $userRepository,
        JobExecutionMessageRepository $jobExecutionMessageRepository
    ) {
        $this->queueJobLauncher = $queueJobLauncher;
        $this->jobInstanceRepository = $jobInstanceRepository;
        $this->userRepository = $userRepository;
        $this->jobExecutionMessageRepository = $jobExecutionMessageRepository;
    }

    public function schedule(): void
    {
        if ($this->isFetchProductsAlreadyScheduled()) {
            return;
        }

        $jobInstance = $this->getFetchProductsJobInstance();
        $user = $this->getFranklinUser();

        $this->queueJobLauncher->launch($jobInstance, $user);
    }

    private function getFetchProductsJobInstance(): JobInstance
    {
        $jobInstance = $this->jobInstanceRepository->findOneByIdentifier(JobInstanceNames::FETCH_PRODUCTS);

        if (!$jobInstance instanceof JobInstance) {
            throw new \RuntimeException(sprintf(
                'The job instance "%s" does not exist. Please contact your administrator.',
                JobInstanceNames::FETCH_PRODUCTS
            ));
        }

        return $jobInstance;
    }

    private function getFranklinUser(): UserInterface
    {
        $user = $this->userRepository->findOneByIdentifier(ProposalAuthor::USERNAME);

        if (!$user instanceof UserInterface) {
            throw new \RuntimeException(sprintf(
                'The user "%s" does not exist. Please contact your administrator.',
                ProposalAuthor::USERNAME
            ));
        }

        return $user;
    }

    private function isFetchProductsAlreadyScheduled(): bool
    {
        $message = $this->jobExecutionMessageRepository->getAvailableJobExecutionMessageFilteredByCodes([JobInstanceNames::FETCH_PRODUCTS]);

        return null !== $message;
    }
}
