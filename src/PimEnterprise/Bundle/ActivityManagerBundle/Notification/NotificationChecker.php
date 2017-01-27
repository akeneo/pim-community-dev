<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ActivityManagerBundle\Notification;

use PimEnterprise\Bundle\ActivityManagerBundle\Doctrine\ORM\Repository\ProjectStatusRepository;
use PimEnterprise\Component\ActivityManager\Model\ProjectInterface;
use PimEnterprise\Component\ActivityManager\Repository\ProjectCompletenessRepositoryInterface;
use PimEnterprise\Component\ActivityManager\Repository\ProjectStatusRepositoryInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Checks if an user should be notified or not.
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class NotificationChecker implements NotificationCheckerInterface
{
    /** @var ProjectCompletenessRepositoryInterface */
    protected $projectCompletenessRepository;

    /** @var ProjectStatusRepository */
    protected $projectStatusRepository;

    /**
     * @param ProjectCompletenessRepositoryInterface $projectCompletenessRepository
     * @param ProjectStatusRepositoryInterface       $projectStatusRepository
     */
    public function __construct(
        ProjectCompletenessRepositoryInterface $projectCompletenessRepository,
        ProjectStatusRepositoryInterface $projectStatusRepository
    ) {
        $this->projectCompletenessRepository = $projectCompletenessRepository;
        $this->projectStatusRepository = $projectStatusRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function isNotifiableForProjectCreation(ProjectInterface $project, UserInterface $user)
    {
        $completeness = $this->projectCompletenessRepository->getProjectCompleteness($project, $user);

        return !$completeness->isComplete() && !$project->isCreated();
    }

    /**
     * {@inheritdoc}
     */
    public function isNotifiableForProjectFinished(ProjectInterface $project, UserInterface $user)
    {
        $actualCompleteness = $this->projectCompletenessRepository->getProjectCompleteness($project, $user);
        $previousCompleteness = $this->projectStatusRepository->wasComplete($project, $user);

        if ($actualCompleteness->isComplete() === $previousCompleteness) {
            return false;
        }

        if (!$previousCompleteness && $actualCompleteness->isComplete() && !$project->isCreated()) {
            $this->projectStatusRepository->setProjectStatus($project, $user, true);
            return true;
        }

        if ($previousCompleteness && !$actualCompleteness->isComplete()) {
            $this->projectStatusRepository->setProjectStatus($project, $user, false);
            return false;
        }

        return $actualCompleteness->isComplete() && !$project->isCreated();
    }
}
