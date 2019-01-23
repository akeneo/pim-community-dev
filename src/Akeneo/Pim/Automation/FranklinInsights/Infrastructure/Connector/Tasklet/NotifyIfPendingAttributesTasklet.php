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
use Akeneo\Pim\Structure\Component\Repository\FamilyRepositoryInterface;
use Akeneo\Platform\Bundle\NotificationBundle\NotifierInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class NotifyIfPendingAttributesTasklet implements TaskletInterface
{
    /** @var SelectUserAndFamilyIdsWithMissingMappingQuery */
    private $userAndFamilyIdsQuery;

    /** @var UserRepositoryInterface */
    private $userRepository;

    /** @var FamilyRepositoryInterface */
    private $familyRepository;

    /** @var SimpleFactoryInterface */
    private $notificationFactory;

    /** @var NotifierInterface */
    private $notifier;

    /** @var StepExecution */
    private $stepExecution;

    /**
     * @param SelectUserAndFamilyIdsWithMissingMappingQuery $userAndFamilyIdsQuery
     * @param UserRepositoryInterface $userRepository
     * @param FamilyRepositoryInterface $familyRepository
     * @param SimpleFactoryInterface $notificationFactory
     * @param NotifierInterface $notifier
     */
    public function __construct(
        SelectUserAndFamilyIdsWithMissingMappingQuery $userAndFamilyIdsQuery,
        UserRepositoryInterface $userRepository,
        FamilyRepositoryInterface $familyRepository,
        SimpleFactoryInterface $notificationFactory,
        NotifierInterface $notifier
    ) {
        $this->userAndFamilyIdsQuery = $userAndFamilyIdsQuery;
        $this->userRepository = $userRepository;
        $this->familyRepository = $familyRepository;
        $this->notificationFactory = $notificationFactory;
        $this->notifier = $notifier;
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

                $family->setLocale($user->getUiLocale());

                $notification = $this->notificationFactory->create();
                $notification
                    ->setType('success')
                    ->setMessage(
                        'akeneo_franklin_insights.entity.attributes_mapping.notification.new_attributes_to_map'
                    )
                    ->setMessageParams(['familyLabel' => $family->getLabel()])
                    ->setRoute('akeneo_franklin_insights_attributes_mapping_edit')
                    ->setRouteParams(['familyCode' => $family->getCode()]);

                $this->notifier->notify($notification, [$user->getUsername()]);
            }
        }
    }
}
