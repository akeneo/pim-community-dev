<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ActivityManager\Job\ProjectCalculation;

use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Pim\Component\Connector\Step\TaskletInterface;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\LocaleAccessRepository;
use PimEnterprise\Component\ActivityManager\Repository\PreProcessingRepositoryInterface;
use PimEnterprise\Component\Security\Attributes;

/**
 * Step executed after a project calculation.
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class PostProjectCalculationTasklet implements TaskletInterface
{
    /** @var PreProcessingRepositoryInterface */
    protected $preProcessingRepository;

    /** @var IdentifiableObjectRepositoryInterface */
    protected $projectRepository;

    /** @var StepExecution */
    protected $stepExecution;

    /** @var LocaleAccessRepository */
    protected $localeAccessRepository;

    /** @var SaverInterface */
    protected $projectSaver;

    /**
     * @param LocaleAccessRepository                $localeAccessRepository
     * @param IdentifiableObjectRepositoryInterface $projectRepository
     * @param SaverInterface                        $projectSaver
     */
    public function __construct(
        LocaleAccessRepository $localeAccessRepository,
        IdentifiableObjectRepositoryInterface $projectRepository,
        SaverInterface $projectSaver
    ) {
        $this->projectRepository = $projectRepository;
        $this->localeAccessRepository = $localeAccessRepository;
        $this->projectSaver = $projectSaver;
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $jobParameters = $this->stepExecution->getJobParameters();
        $projectCode = $jobParameters->get('project_code');
        $project = $this->projectRepository->findOneByIdentifier($projectCode);

        $projectContributorGroups = $project->getUserGroups()->toArray();

        $localeContributorGroups = $this->localeAccessRepository->getGrantedUserGroups(
            $project->getLocale(),
            Attributes::EDIT_ITEMS
        );

        $groupsToRemove = array_diff($projectContributorGroups, $localeContributorGroups);

        foreach ($groupsToRemove as $groupToRemove) {
            $project->removeUserGroup($groupToRemove);
        }

        $this->projectSaver->save($project);
    }
}
