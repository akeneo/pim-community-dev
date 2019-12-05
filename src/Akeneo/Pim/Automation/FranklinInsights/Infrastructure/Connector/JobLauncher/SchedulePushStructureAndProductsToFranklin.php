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

use Akeneo\Pim\Automation\FranklinInsights\Application\QualityHighlights\SchedulePushStructureAndProductsToFranklinInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Proposal\ValueObject\ProposalAuthor;
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\ValueObject\BatchSize;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Connector\JobInstanceNames;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Connector\JobParameters\PushStructureAndProductsToFranklinParameters;
use Akeneo\Tool\Bundle\BatchBundle\Job\JobInstanceRepository;
use Akeneo\Tool\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;
use Symfony\Component\Security\Core\User\UserInterface;

final class SchedulePushStructureAndProductsToFranklin implements SchedulePushStructureAndProductsToFranklinInterface
{
    /** @var JobLauncherInterface */
    private $queueJobLauncher;

    /** @var JobInstanceRepository */
    private $jobInstanceRepository;

    /** @var UserRepositoryInterface */
    private $userRepository;

    public function __construct(
        JobLauncherInterface $queueJobLauncher,
        JobInstanceRepository $jobInstanceRepository,
        UserRepositoryInterface $userRepository
    ) {
        $this->queueJobLauncher = $queueJobLauncher;
        $this->jobInstanceRepository = $jobInstanceRepository;
        $this->userRepository = $userRepository;
    }

    public function schedule(BatchSize $attributesBatchSize, BatchSize $familiesBatchSize, BatchSize $productsBatchSize): void
    {
        $jobInstance = $this->getJobInstance();
        $user = $this->getFranklinUser();
        $jobParameters = [
            PushStructureAndProductsToFranklinParameters::ATTRIBUTES_BATCH_SIZE => $attributesBatchSize->toInt(),
            PushStructureAndProductsToFranklinParameters::FAMILIES_BATCH_SIZE => $familiesBatchSize->toInt(),
            PushStructureAndProductsToFranklinParameters::PRODUCTS_BATCH_SIZE => $productsBatchSize->toInt(),
        ];

        $this->queueJobLauncher->launch($jobInstance, $user, $jobParameters);
    }

    private function getJobInstance(): JobInstance
    {
        $jobInstance = $this->jobInstanceRepository->findOneByIdentifier(JobInstanceNames::PUSH_STRUCTURE_AND_PRODUCTS);

        if (!$jobInstance instanceof JobInstance) {
            throw new \RuntimeException(sprintf(
                'The job instance "%s" does not exist. Please contact your administrator.',
                JobInstanceNames::PUSH_STRUCTURE_AND_PRODUCTS
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
}
