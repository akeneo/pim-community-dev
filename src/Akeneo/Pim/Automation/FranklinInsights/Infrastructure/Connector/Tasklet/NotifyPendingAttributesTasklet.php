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

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Connector\Tasklet;

use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Persistence\Query\Doctrine\SelectUserAndFamilyIdsWithMissingMappingQuery;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\UserNotification\NotifyUserAboutMissingMapping;
use Akeneo\Pim\Structure\Component\Repository\FamilyRepositoryInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class NotifyPendingAttributesTasklet implements TaskletInterface
{
    /** @var SelectUserAndFamilyIdsWithMissingMappingQuery */
    private $userAndFamilyIdsQuery;

    /** @var UserRepositoryInterface */
    private $userRepository;

    /** @var FamilyRepositoryInterface */
    private $familyRepository;

    /** @var NotifyUserAboutMissingMapping */
    private $notifyUserAboutMissingMapping;

    /** @var StepExecution */
    private $stepExecution;

    /**
     * @param SelectUserAndFamilyIdsWithMissingMappingQuery $userAndFamilyIdsQuery
     * @param UserRepositoryInterface $userRepository
     * @param FamilyRepositoryInterface $familyRepository
     * @param NotifyUserAboutMissingMapping $notifyUserAboutMissingMapping
     */
    public function __construct(
        SelectUserAndFamilyIdsWithMissingMappingQuery $userAndFamilyIdsQuery,
        UserRepositoryInterface $userRepository,
        FamilyRepositoryInterface $familyRepository,
        NotifyUserAboutMissingMapping $notifyUserAboutMissingMapping
    ) {
        $this->userAndFamilyIdsQuery = $userAndFamilyIdsQuery;
        $this->userRepository = $userRepository;
        $this->familyRepository = $familyRepository;
        $this->notifyUserAboutMissingMapping = $notifyUserAboutMissingMapping;
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution): void
    {
        $this->stepExecution = $stepExecution;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(): void
    {
        $userIdsAndFamilyIds = $this->userAndFamilyIdsQuery->execute();

        foreach ($userIdsAndFamilyIds as $userId => $familyIds) {
            $user = $this->userRepository->find($userId);
            if (null === $user) {
                continue;
            }

            foreach ($familyIds as $familyId) {
                $family = $this->familyRepository->find($familyId);
                if (null === $family) {
                    continue;
                }

                $this->notifyUserAboutMissingMapping->forFamily($user, $family);
            }
        }
    }
}
