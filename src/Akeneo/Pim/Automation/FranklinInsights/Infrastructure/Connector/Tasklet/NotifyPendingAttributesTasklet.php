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

use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\Repository\FamilyRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Persistence\Query\Doctrine\FilterUsersToNotifyAboutGivenFamilyMissingMappingQuery;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Persistence\Query\Doctrine\SelectUsersAbleToCompleteFamiliesMissingMappingQuery;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\UserNotification\NotifyUserAboutMissingMapping;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class NotifyPendingAttributesTasklet implements TaskletInterface
{
    /** @var SelectUsersAbleToCompleteFamiliesMissingMappingQuery */
    private $userIdsAndFamilyCodesQuery;

    /** @var UserRepositoryInterface */
    private $userRepository;

    /** @var FamilyRepositoryInterface */
    private $familyRepository;

    /** @var NotifyUserAboutMissingMapping */
    private $notifyUserAboutMissingMapping;

    /** @var StepExecution */
    private $stepExecution;

    /** @var FilterUsersToNotifyAboutGivenFamilyMissingMappingQuery */
    private $filterUsersToNotify;

    /**
     * @param SelectUsersAbleToCompleteFamiliesMissingMappingQuery $userIdsAndFamilyCodesQuery
     * @param UserRepositoryInterface $userRepository
     * @param FamilyRepositoryInterface $familyRepository
     * @param NotifyUserAboutMissingMapping $notifyUserAboutMissingMapping
     * @param FilterUsersToNotifyAboutGivenFamilyMissingMappingQuery $filterUsersToNotify
     */
    public function __construct(
        SelectUsersAbleToCompleteFamiliesMissingMappingQuery $userIdsAndFamilyCodesQuery,
        UserRepositoryInterface $userRepository,
        FamilyRepositoryInterface $familyRepository,
        NotifyUserAboutMissingMapping $notifyUserAboutMissingMapping,
        FilterUsersToNotifyAboutGivenFamilyMissingMappingQuery $filterUsersToNotify
    ) {
        $this->userIdsAndFamilyCodesQuery = $userIdsAndFamilyCodesQuery;
        $this->userRepository = $userRepository;
        $this->familyRepository = $familyRepository;
        $this->notifyUserAboutMissingMapping = $notifyUserAboutMissingMapping;
        $this->filterUsersToNotify = $filterUsersToNotify;
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
        $familyCodesAndUserIds = $this->userIdsAndFamilyCodesQuery->execute();
        foreach ($familyCodesAndUserIds as $rawFamilyCode => $userIds) {
            $familyCode = new FamilyCode($rawFamilyCode);
            $family = $this->familyRepository->findOneByIdentifier($familyCode);
            if (null === $family) {
                continue;
            }
            $usersToNotify = $this->filterUsersToNotify->filter($familyCode, $userIds);
            foreach ($usersToNotify as $userId) {
                $user = $this->userRepository->find($userId);
                if (null === $user) {
                    continue;
                }

                $this->notifyUserAboutMissingMapping->forFamily($user, $family);
            }
        }
    }
}
